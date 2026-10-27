import express from 'express';
import session from 'express-session';
import bcrypt from 'bcryptjs';
import nodemailer from 'nodemailer';
import { randomBytes, randomInt, createHash, timingSafeEqual } from 'node:crypto';
import { fileURLToPath } from 'node:url';
import { createSupabase } from './supabase.js';

const publicDir = fileURLToPath(new URL('../public/', import.meta.url));
const safeFields = 'id,name,email,role,status';
const caregiverFields = 'id,name,email,phone,cpf,specialties,status,created_at';
const fail = (message, status = 400) => { throw Object.assign(new Error(message), { status }); };
const digest = value => createHash('sha256').update(value).digest();
const digits = value => String(value || '').replace(/\D/g, '');
const publicUser = user => Object.fromEntries(safeFields.split(',').map(key => [key, user[key]]));
const regenerate = req => new Promise((resolve, reject) => req.session.regenerate(error => error ? reject(error) : resolve()));
function validatePassword(data) {
  if (typeof data.password !== 'string' || [...data.password].length < 6 || Buffer.byteLength(data.password) > 72) fail('A senha deve ter de 6 a 72 bytes.');
  if (data.password !== data.password_confirm) fail('As senhas não coincidem.');
}
function validateFields(data, fields) {
  for (const field of fields) {
    if (typeof data[field] !== 'string' || !data[field].trim() || data[field].length > 255) fail('Preencha todos os campos obrigatórios corretamente.');
  }
  if (fields.includes('email') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) fail('Informe um e-mail válido.');
}

export function createApp({ config = process.env, db = createSupabase({ url: config.SUPABASE_URL, key: config.SUPABASE_KEY }), sendMail, sessionStore } = {}) {
  const app = express();
  const production = config.NODE_ENV === 'production';
  if (production && (!config.SESSION_SECRET || config.SESSION_SECRET.length < 32)) throw new Error('Configure SESSION_SECRET com pelo menos 32 caracteres.');
  const origin = config.APP_URL || 'http://localhost:3000';
  const preview = !production && config.SHOW_CODE_FALLBACK === 'true';
  const mail = sendMail || (async ({ email, name, token }) => {
    if (!config.SMTP_HOST || !config.MAIL_FROM) {
      if (preview) return;
      fail('Configure SMTP_HOST e MAIL_FROM para enviar códigos de recuperação.', 503);
    }
    const transport = nodemailer.createTransport({ host: config.SMTP_HOST, port: Number(config.SMTP_PORT || 587), secure: config.SMTP_SECURE === 'true',
      auth: config.SMTP_USER ? { user: config.SMTP_USER, pass: config.SMTP_PASS } : undefined });
    await transport.sendMail({ from: config.MAIL_FROM, to: email, subject: 'Código de recuperação — VivaBem', text: `Olá, ${name}. Seu código VivaBem é ${token}. Ele expira em 10 minutos.` });
  });
  app.disable('x-powered-by');
  app.use((req, res, next) => {
    res.set({ 'X-Content-Type-Options': 'nosniff', 'Referrer-Policy': 'same-origin', 'X-Frame-Options': 'DENY',
      'Content-Security-Policy': "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'" });
    next();
  });
  app.use(express.json({ limit: '16kb' }));
  app.use(session({ name: 'vivabem.sid', secret: config.SESSION_SECRET || randomBytes(48).toString('hex'), store: sessionStore, resave: false, saveUninitialized: false,
    cookie: { httpOnly: true, sameSite: 'lax', secure: production, maxAge: 8 * 60 * 60 * 1000 } }));
  // Session-bound CSRF token and Origin check protect every state-changing API.
  app.use('/api', (req, res, next) => {
    res.set('Cache-Control', 'no-store');
    req.session.csrf ||= randomBytes(24).toString('hex');
    if (!['GET', 'HEAD', 'OPTIONS'].includes(req.method)) {
      if (req.get('Origin') && req.get('Origin') !== new URL(origin).origin) return res.status(403).json({ error: 'Origem não permitida.' });
      const token = req.get('X-CSRF-Token') || '';
      if (Buffer.byteLength(token) !== Buffer.byteLength(req.session.csrf) || !timingSafeEqual(Buffer.from(token), Buffer.from(req.session.csrf))) return res.status(403).json({ error: 'Sessão expirada. Atualize a página.' });
    }
    next();
  });
  // Bound, in-memory limiter for development/single-instance deployments.
  const attempts = new Map();
  app.use('/api', (req, res, next) => {
    if (req.method === 'GET') return next();
    const key = req.ip;
    const now = Date.now();
    for (const [id, item] of attempts) if (item.expires < now) attempts.delete(id);
    const item = attempts.get(key) || { count: 0, expires: now + 60000 };
    attempts.set(key, item);
    if (++item.count > 30) return res.status(429).json({ error: 'Muitas solicitações. Aguarde um minuto.' });
    next();
  });
  const first = async params => (await db('users', { params: { select: safeFields, limit: 1, ...params } }))?.[0];
  const requireUser = async (req, res, next) => {
    if (!req.session.user) return res.status(401).json({ error: 'Entre para continuar.' });
    // Recheck status/role so deactivated accounts lose access immediately.
    const user = await first({ id: `eq.${req.session.user.id}` });
    if (!user || user.status !== 'active') {
      delete req.session.user;
      return res.status(401).json({ error: 'Conta indisponível. Entre novamente.' });
    }
    req.session.user = publicUser(user);
    next();
  };
  const company = async (req, res, next) => {
    await requireUser(req, res, () => req.session.user.role === 'company' ? next() : res.status(403).json({ error: 'Área exclusiva de clínicas.' }));
  };
  const login = async (req, user) => {
    if (user.status !== 'active') fail('Esta conta está desativada.', 403);
    await regenerate(req);
    req.session.user = publicUser(user);
  };
  const unique = async (field, value, id) => {
    if (await first({ [field]: `eq.${value}`, ...(id ? { id: `neq.${id}` } : {}) })) fail(`${field === 'email' ? 'E-mail' : field.toUpperCase()} já cadastrado.`, 409);
  };
  app.get('/api/health', (req, res) => res.json({ status: 'ok', databaseConfigured: Boolean(config.SUPABASE_URL && config.SUPABASE_KEY) }));
  app.get('/api/session', async (req, res) => {
    if (req.session.user) {
      const user = await first({ id: `eq.${req.session.user.id}` });
      req.session.user = user?.status === 'active' ? publicUser(user) : null;
    }
    const pending = req.session.reset;
    res.json({ user: req.session.user || null, csrf: req.session.csrf,
      reset: pending && pending.expires > Date.now() ? { email: pending.email.replace(/^(.).+(@.*)$/, '$1***$2'), verified: pending.verified, ...(preview && pending.userId ? { preview: pending.preview } : {}) } : null });
  });
  app.post('/api/login', async (req, res) => {
    const { email, password } = req.body;
    validateFields(req.body, ['email', 'password']);
    const user = await first({ email: `eq.${email.trim().toLowerCase()}`, select: `${safeFields},password,auth_provider` });
    if (!user || !await bcrypt.compare(password, String(user.password).replace(/^\$2y\$/, '$2b$'))) fail('E-mail ou senha incorretos.', 401);
    await login(req, user);
    res.json({ redirect: '/pages/dashboard.html' });
  });
  for (const isCompany of [false, true]) {
    app.post(isCompany ? '/api/register-company' : '/api/register', async (req, res) => {
      const data = req.body;
      validateFields(data, isCompany ? ['company_name','legal_name','cnpj','email','phone','contact_name'] : ['name','email']);
      validatePassword(data);
      const email = data.email.trim().toLowerCase();
      await unique('email', email);
      const record = { name: (isCompany ? data.company_name : data.name).trim(), email, password: await bcrypt.hash(data.password, 12), role: isCompany ? 'company' : 'family', status: 'active' };
      if (isCompany) {
        if (digits(data.cnpj).length !== 14) fail('Informe um CNPJ com 14 dígitos.');
        await unique('cnpj', digits(data.cnpj));
        Object.assign(record, { company_name: data.company_name.trim(), legal_name: data.legal_name.trim(), cnpj: digits(data.cnpj), phone: data.phone.trim(), contact_name: data.contact_name.trim() });
      }
      await db('users', { method: 'POST', data: record });
      res.status(201).json({ message: 'Conta criada com sucesso. Faça login para continuar.', redirect: '/login.html' });
    });
  }
  app.post('/api/logout', (req, res, next) => req.session.destroy(error => {
    if (error) return next(error);
    res.clearCookie('vivabem.sid').json({ redirect: '/login.html' });
  }));
  async function resetToken(req, user) {
    const token = String(randomInt(100000,1000000));
    const pending = { userId: user?.id || 0, email: user.email, name: user.name || 'Usuário', hash: digest(token).toString('hex'), expires: Date.now()+600000, created: Date.now(), attempts: 0, verified: false };
    if (pending.userId) {
      await mail({ email: pending.email, name: pending.name, token });
      if (preview) pending.preview = token;
    }
    req.session.reset = pending;
  }
  app.post('/api/forgot-password', async (req, res) => {
    validateFields(req.body, ['email']);
    if (req.session.reset && Date.now()-req.session.reset.created < 45000) fail('Aguarde 45 segundos antes de solicitar outro código.', 429);
    const email = req.body.email.trim().toLowerCase();
    const user = await first({ email: `eq.${email}` });
    await resetToken(req, user || { email });
    res.json({ redirect: '/verify.html', message: 'Se este e-mail estiver cadastrado, enviamos um código de verificação.' });
  });
  const pendingReset = req => {
    if (!req.session.reset || req.session.reset.expires < Date.now()) { delete req.session.reset; fail('Solicite um novo código de recuperação.'); }
    return req.session.reset;
  };
  app.post('/api/resend-token', async (req, res) => {
    const reset = pendingReset(req);
    if (Date.now()-reset.created < 45000) fail('Aguarde 45 segundos antes de reenviar.', 429);
    await resetToken(req, { id: reset.userId, email: reset.email, name: reset.name });
    res.json({ redirect: '/verify.html', message: 'Se este e-mail estiver cadastrado, enviamos um novo código.' });
  });
  app.post('/api/verify', (req, res) => {
    const reset = pendingReset(req);
    if (++reset.attempts > 5) { delete req.session.reset; fail('Muitas tentativas inválidas. Solicite outro código.'); }
    if (!/^\d{6}$/.test(req.body.token || '') || !timingSafeEqual(digest(req.body.token), Buffer.from(reset.hash,'hex')) || !reset.userId) fail('Código inválido.');
    reset.verified = true;
    res.json({ redirect: '/reset_password.html' });
  });
  app.post('/api/reset-password', async (req, res) => {
    const reset = pendingReset(req);
    if (!reset.verified || !reset.userId) fail('Confirme o código de recuperação.', 403);
    validatePassword(req.body);
    await db('users', { method: 'PATCH', params: { id: `eq.${reset.userId}` }, data: { password: await bcrypt.hash(req.body.password,12) } });
    delete req.session.reset;
    res.json({ redirect: '/login.html', message: 'Senha atualizada. Faça login com a nova senha.' });
  });
  const owned = async (req, id) => {
    if (!/^\d+$/.test(String(id))) fail('Cuidador inválido.');
    const params = { id: `eq.${id}`, role: 'eq.caregiver', clinic_id: `eq.${req.session.user.id}` };
    const user = await first({ ...params, select: caregiverFields });
    if (!user) fail('Cuidador não encontrado nesta clínica.', 404);
    return { user, params };
  };
  app.get('/api/caregivers', company, async (req, res) => {
    const users = await db('users', { params: { select: caregiverFields, role:'eq.caregiver', clinic_id:`eq.${req.session.user.id}`, order:'name.asc' } });
    res.json({ caregivers: users });
  });
  app.post('/api/caregiver', company, async (req, res) => {
    const data = req.body;
    validateFields(data, ['name','cpf','email','phone','specialties']);
    if (digits(data.cpf).length !== 11) fail('Informe um CPF com 11 dígitos.');
    let params;
    if (data.id) ({ params } = await owned(req, data.id));
    if (!data.id || data.password || data.password_confirm) validatePassword(data);
    const email = data.email.trim().toLowerCase();
    await unique('email', email, data.id);
    await unique('cpf', digits(data.cpf), data.id);
    const record = { name:data.name.trim(), cpf:digits(data.cpf), email, phone:data.phone.trim(), specialties:data.specialties.trim() };
    if (data.password) record.password = await bcrypt.hash(data.password,12);
    if (!data.id) Object.assign(record, { role:'caregiver', status:'active', clinic_id:req.session.user.id });
    await db('users', { method:data.id ? 'PATCH' : 'POST', params, data:record });
    res.json({ message: 'Cuidador salvo com sucesso.' });
  });
  for (const action of ['toggle','unlink']) app.post(`/api/caregivers/:id/${action}`, company, async (req, res) => {
    const { user, params } = await owned(req, req.params.id);
    await db('users', { method:'PATCH', params, data: action === 'toggle' ? { status:user.status === 'active' ? 'inactive' : 'active' } : { clinic_id:null } });
    res.json({ message: action === 'toggle' ? 'Status atualizado.' : 'Vínculo removido.' });
  });
  app.get('/api/auth/google', (req, res) => {
    if (!config.SUPABASE_URL) return res.redirect('/login.html?error=google-config');
    const verifier = randomBytes(32).toString('base64url');
    const state = randomBytes(24).toString('hex');
    req.session.oauth = { verifier, state, expires: Date.now()+600000 };
    const callback = new URL('/api/auth/google/callback', origin);
    callback.searchParams.set('state',state);
    const target = new URL('/auth/v1/authorize', config.SUPABASE_URL);
    target.search = new URLSearchParams({ provider:'google', redirect_to:callback.href, scopes:'openid email profile', code_challenge:digest(verifier).toString('base64url'), code_challenge_method:'s256' });
    res.redirect(target.href);
  });
  app.get('/api/auth/google/callback', async (req, res) => {
    const oauth = req.session.oauth;
    delete req.session.oauth;
    try {
      if (!oauth || oauth.expires < Date.now() || oauth.state !== req.query.state || !req.query.code || req.query.error) fail('Sessão Google inválida.');
      const result = await db('token', { auth:true, method:'POST', params:{ grant_type:'pkce' }, data:{ auth_code:req.query.code, code_verifier:oauth.verifier } });
      const authUser = result.user;
      if (!authUser?.email || !authUser.email_confirmed_at) fail('E-mail não confirmado.');
      const googleIdentity = authUser.identities?.find(identity => identity.provider === 'google');
      if (!googleIdentity) fail('Identidade Google não confirmada.');
      const email = authUser.email.toLowerCase();
      const googleId = googleIdentity.id || authUser.id;
      let user = await first({ google_id:`eq.${googleId}` }) || await first({ email:`eq.${email}` });
      if (!user) {
        [user] = await db('users',{ method:'POST', data:{ name:authUser.user_metadata?.full_name || email.split('@')[0], email, password:await bcrypt.hash(randomBytes(32).toString('hex'),12), role:'family', status:'active', auth_provider:'google', google_id:googleId } });
      } else {
        await db('users',{ method:'PATCH', params:{ id:`eq.${user.id}` }, data:{ google_id:googleId } });
      }
      await login(req,user);
      res.redirect('/pages/dashboard.html');
    } catch { res.redirect('/login.html?error=google-login'); }
  });
  app.use('/api', (req,res) => res.status(404).json({ error:'Operação não encontrada.' }));
  // Preserve old bookmarks without executing or exposing PHP sources.
  app.get(/\.php$/, (req,res) => {
    const target = req.path.replace(/\.php$/,'.html');
    if (target.startsWith('/actions/') || target.startsWith('/config/') || target.startsWith('/includes/')) return res.sendStatus(404);
    res.redirect(301,target);
  });
  app.use(express.static(publicDir, { dotfiles:'deny' }));
  app.use((req,res) => res.status(404).type('html').send('<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Página não encontrada</title><h1>Página não encontrada</h1><a href="/">Voltar ao início</a></html>'));
  app.use((error, req, res, next) => {
    if (res.headersSent) return next(error);
    const status = error.status || 500;
    res.status(status).json({ error: status < 500 || status === 503 || status === 502 ? error.message : 'Não foi possível concluir a operação. Tente novamente.' });
  });
  return app;
}
