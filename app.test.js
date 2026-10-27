import { test } from 'node:test';
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { existsSync } from 'node:fs';
import { readFile, readdir } from 'node:fs/promises';
import { chromium } from '@playwright/test';
import { createApp } from './server.js';

function database() {
  const users = [];
  return { users, db: async (path, { method = 'GET', data, params = {} } = {}) => {
    assert.equal(path, 'users');
    const matching = users.filter(user => Object.entries(params).every(([key,value]) => {
      if (['select','limit','order'].includes(key)) return true;
      const [op,...parts] = String(value).split('.');
      return op === 'neq' ? String(user[key]) !== parts.join('.') : String(user[key]) === parts.join('.');
    }));
    if (method === 'POST') { const user = { id: users.length+1, ...data }; users.push(user); return [user]; }
    if (method === 'PATCH') { matching.forEach(user => Object.assign(user,data)); return matching; }
    return matching.map(user => Object.fromEntries((params.select || Object.keys(user).join(',')).split(',').map(key => [key,user[key]])));
  } };
}
async function setup(t, config = {}) {
  const memory = database();
  const messages = [];
  const server = createServer();
  server.listen(0,'127.0.0.1');
  await new Promise(resolve => server.once('listening',resolve));
  t.after(() => new Promise(resolve => { server.closeAllConnections(); server.close(resolve); }));
  const url = `http://127.0.0.1:${server.address().port}`;
  server.on('request',createApp({ config: { APP_URL:url, NODE_ENV:'test', SHOW_CODE_FALLBACK:'true', ...config }, db:memory.db, sendMail: async message => messages.push(message) }));
  return { ...memory, messages, url };
}
function client(url) {
  let cookie = '', csrf = '';
  return async (path, data, token) => {
    const response = await fetch(url+'/api/'+path, { headers:{ Cookie:cookie, ...(data ? {'Content-Type':'application/json','X-CSRF-Token':token ?? csrf} : {}) }, method:data ? 'POST':'GET', body:data ? JSON.stringify(data):undefined, redirect:'manual' });
    if (response.headers.get('set-cookie')) cookie = response.headers.get('set-cookie').split(';')[0];
    const result = await response.json();
    if (result.csrf) csrf = result.csrf;
    return { status:response.status, ...result };
  };
}
const account = { name:'Pessoa Teste', email:'pessoa@example.test', password:'segredo123', password_confirm:'segredo123' };
const clinic = { ...account, company_name:'Clínica Teste',legal_name:'Clínica Teste Ltda',cnpj:'12345678000190',phone:'11999999999',contact_name:'Responsável' };
const caregiver = { ...account, name:'Cuidador', email:'cuidador@example.test', cpf:'12345678901', phone:'11999999999', specialties:'Geriatria' };

test('cadastro, bcrypt PHP, sessão, logout e dados privados', async t => {
  const {url,users} = await setup(t);
  const api=client(url);
  await api('session');
  assert.equal((await api('register',account,'wrong')).status,403);
  assert.equal((await api('register',{...account,password_confirm:'diferente'})).status,400);
  assert.equal((await api('register',account)).status,201);
  assert.notEqual(users[0].password, account.password);
  users[0].password=users[0].password.replace('$2b$','$2y$');
  assert.equal((await api('login',{email:account.email,password:'errada'})).status,401);
  assert.equal((await api('login',account)).status,200);
  const session=await api('session');
  assert.equal(session.user.name,account.name);
  assert.equal(session.user.password,undefined);
  assert.equal((await api('caregivers')).status,403);
  assert.equal((await api('logout',{})).status,200);
  assert.equal((await api('session')).user,null);
  for (const path of ['/config/supabase.php','/.env','/.env.example','/.git/config','/server.js','/index.js','/supabase.js','/app.test.js','/supabase.sql','/schema.sql','/package.json','/package-lock.json','/README.md','/node_modules/express/package.json','/storage/mail.log']) assert.equal((await fetch(url+path)).status,404);
});

test('recuperação: código inválido, confirmação e nova senha', async t => {
  const {url,messages}=await setup(t);
  const api=client(url); await api('session'); await api('register',account);
  assert.equal((await api('reset-password',account)).status,400);
  assert.equal((await api('forgot-password',{email:account.email})).status,200);
  assert.equal(messages.length,1);
  assert.equal((await api('resend-token',{})).status,429);
  assert.equal((await api('verify',{token:'000000'})).status,400);
  assert.equal((await api('verify',{token:messages[0].token})).status,200);
  const changed={password:'novaSenha123',password_confirm:'novaSenha123'};
  assert.equal((await api('reset-password',changed)).status,200);
  assert.equal((await api('reset-password',changed)).status,400);
  assert.equal((await api('login',account)).status,401);
  assert.equal((await api('login',{email:account.email,password:changed.password})).status,200);
});

test('clínicas: cadastro, edição, status, isolamento e remoção do vínculo', async t => {
  const {url,users}=await setup(t);
  const first=client(url), other=client(url);
  await first('session'); await other('session');
  await first('register-company',clinic); await first('login',clinic); await first('session');
  await other('register-company',{...clinic,email:'outra@example.test',cnpj:'98765432000190'});
  await other('login',{email:'outra@example.test',password:account.password}); await other('session');
  assert.equal((await first('caregiver',caregiver)).status,200);
  const person=users.find(user=>user.role==='caregiver');
  assert.equal((await other(`caregivers/${person.id}/toggle`,{})).status,404);
  assert.equal((await other('caregiver',{...caregiver,id:String(person.id),name:'Invasor'})).status,404);
  assert.equal((await first('caregiver',{...caregiver,id:String(person.id),name:'Novo nome',password:'',password_confirm:''})).status,200);
  assert.equal((await first('caregivers')).caregivers[0].name,'Novo nome');
  assert.equal((await first('caregivers')).caregivers[0].password,undefined);
  await first(`caregivers/${person.id}/toggle`,{});
  const personClient=client(url); await personClient('session');
  assert.equal((await personClient('login',caregiver)).status,403);
  await first(`caregivers/${person.id}/toggle`,{});
  assert.equal((await personClient('login',caregiver)).status,200);
  await first(`caregivers/${person.id}/toggle`,{});
  assert.equal((await personClient('session')).user,null);
  await first(`caregivers/${person.id}/unlink`,{});
  assert.equal((await first('caregivers')).caregivers.length,0);
});

test('limite de tentativas e código de uso único', async t => {
  const {url,messages}=await setup(t);
  const api=client(url); await api('session'); await api('register',account);
  await api('forgot-password',{email:account.email});
  for(let i=0;i<6;i++) await api('verify',{token:'000000'});
  assert.equal((await api('verify',{token:messages[0].token})).status,400);
});

test('navegador: HTML, menu móvel, formulários, painel e cuidadores', async t => {
  const {url}=await setup(t);
  const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH || (existsSync('/usr/bin/chromium') ? '/usr/bin/chromium' : undefined),headless:true,args:['--no-sandbox']});
  t.after(()=>browser.close());
  const page=await browser.newPage({viewport:{width:390,height:844}});
  const errors=[]; page.on('pageerror',error=>errors.push(error.message));
  await page.goto(url);
  await page.locator('.nav-toggle').click();
  assert.equal(await page.locator('.nav-toggle').getAttribute('aria-expanded'),'true');
  await page.goto(url+'/register_company.html');
  for(const [key,value] of Object.entries(clinic)) if(key!=='name') await page.locator(`[name="${key}"]`).fill(value);
  await page.getByRole('button',{name:'Criar conta da clínica'}).click();
  await page.waitForURL('**/login.html');
  await page.locator('[name="email"]').fill(clinic.email); await page.locator('[name="password"]').fill(clinic.password);
  await page.getByRole('button',{name:'Entrar',exact:true}).click();
  await page.waitForURL('**/dashboard.html');
  await page.getByRole('link',{name:'Gerenciar cuidadores'}).click();
  for(const [key,value] of Object.entries(caregiver)) await page.locator(`[name="${key}"]`).fill(value);
  await page.getByRole('button',{name:'Salvar cuidador'}).click();
  await page.getByRole('button',{name:'Editar',exact:true}).waitFor();
  await page.getByRole('button',{name:'Editar',exact:true}).click();
  await page.locator('[name="name"]').fill('Cuidador atualizado');
  await page.getByRole('button',{name:'Salvar cuidador'}).click();
  await page.getByRole('cell',{name:'Cuidador atualizado',exact:true}).waitFor();
  await page.getByRole('button',{name:'Desativar',exact:true}).click();
  await page.getByRole('cell',{name:'Inativo',exact:true}).waitFor();
  page.on('dialog',dialog=>dialog.accept());
  await page.getByRole('button',{name:'Remover vínculo'}).click();
  await page.getByText('Nenhum cuidador cadastrado ainda.').waitFor();
  await page.goto(url+'/quemsomos.html');
  assert.equal(await page.locator('[data-team-sector]').count(),5);
  await page.locator('.team-sector__header').first().click();
  assert.equal(await page.locator('.team-sector__header').first().getAttribute('aria-expanded'),'true');
  assert.deepEqual(errors,[]);
});

test('Google: PKCE, validação de estado e callback com provedor simulado', async t => {
  const memory=database();
  const server=createServer(); server.listen(0,'127.0.0.1');
  await new Promise(resolve=>server.once('listening',resolve));
  t.after(()=>new Promise(resolve=>{server.closeAllConnections();server.close(resolve);}));
  const url=`http://127.0.0.1:${server.address().port}`;
  let exchanged=false;
  server.on('request',createApp({config:{APP_URL:url,NODE_ENV:'test',SUPABASE_URL:'https://example.supabase.co'},db:async (path,options)=>{
    if(path==='token') {
      exchanged=true;
      assert.equal(options.auth,true);
      assert.equal(options.params.grant_type,'pkce');
      assert.equal(options.data.auth_code,'valid-code');
      assert.ok(options.data.code_verifier.length>=43);
      return {user:{id:'auth-id',email:'google@example.test',email_confirmed_at:new Date().toISOString(),user_metadata:{full_name:'Pessoa Google'},identities:[{provider:'google',id:'google-id'}]}};
    }
    return memory.db(path,options);
  }}));
  const start=await fetch(url+'/api/auth/google',{redirect:'manual'});
  const cookie=start.headers.get('set-cookie').split(';')[0];
  const target=new URL(start.headers.get('location'));
  assert.equal(target.searchParams.get('code_challenge_method'),'s256');
  assert.ok(target.searchParams.get('code_challenge').length>=43);
  const callback=new URL(target.searchParams.get('redirect_to'));
  const invalid=await fetch(callback.href+'&code=valid-code&state=wrong',{headers:{Cookie:cookie},redirect:'manual'});
  assert.equal(invalid.status,302);
  assert.equal(exchanged,false);
  const restart=await fetch(url+'/api/auth/google',{headers:{Cookie:cookie},redirect:'manual'});
  const retryCallback=new URL(new URL(restart.headers.get('location')).searchParams.get('redirect_to'));
  retryCallback.searchParams.set('code','valid-code');
  const success=await fetch(retryCallback,{headers:{Cookie:cookie},redirect:'manual'});
  assert.equal(success.headers.get('location'),'/dashboard.html');
  assert.equal(exchanged,true);
  assert.equal(memory.users[0].auth_provider,'google');
});


test('estrutura plana: páginas, assets e caminhos antigos', async t => {
  const {url}=await setup(t);
  const files=await readdir(new URL('./',import.meta.url));
  const pages=files.filter(file=>file.endsWith('.html'));
  assert.equal(pages.length,12);
  for(const name of pages){
    const response=await fetch(`${url}/${name}`);
    assert.equal(response.status,200,name);
    const html=await response.text();
    for(const [,path] of html.matchAll(/(?:href|src)="(\/[^"?#]*)/g)){
      if(path.startsWith('/api/'))continue;
      assert.equal(path.slice(1).includes('/'),false,path);
      assert.equal((await fetch(url+path)).status,200,path);
    }
  }
  const css=await readFile(new URL('./main.css',import.meta.url),'utf8');
  assert.ok(css.includes('.site-header'));
  for(const path of ['/dashboard.php','/pages/dashboard.php','/pages/dashboard.html']){
    const response=await fetch(url+path,{redirect:'manual'});
    assert.equal(response.status,301);
    assert.equal(response.headers.get('location'),'/dashboard.html');
  }
});
