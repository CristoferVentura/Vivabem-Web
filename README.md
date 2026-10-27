# VivaBem Web

Interface em **HTML e CSS**, com funcionalidades em **JavaScript** no navegador e uma API **Node.js**. Não requer PHP, XAMPP, bundler ou framework de interface. As páginas, imagens, cores, conteúdo e componentes do projeto original foram preservados; animações usam APIs nativas do navegador.

## Executar

Requer Node.js 24 e npm. No diretório do projeto:

```sh
npm ci
cp .env.example .env
# Preencha .env de forma segura, sem versionar credenciais.
npm run dev
```

`npm start` executa sem modo de observação. A porta padrão é 3000. A interface pública e `/api/health` funcionam sem banco; cadastro, login e cuidadores exigem Supabase. Sirva o projeto pelo Node: abrir HTML como arquivo ou hospedar apenas os arquivos estáticos não executa a API.

## Configuração

- `APP_URL`: origem completa do site, usada na proteção de origem e no retorno do Google. Em desenvolvimento: `http://localhost:3000`.
- `SUPABASE_URL`: URL HTTPS do projeto existente.
- `SUPABASE_KEY`: chave **service_role**, somente no servidor. Nunca colocar em arquivos públicos, no JavaScript do navegador, em commits ou logs. A chave anon antiga foi removida do código; não é adequada para acessar a tabela de senhas.
- `SESSION_SECRET`: segredo aleatório persistente de pelo menos 32 caracteres. Obrigatório com `NODE_ENV=production`.
- Recuperação por e-mail: `SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE`, `SMTP_USER`, `SMTP_PASS`, `MAIL_FROM`. Configure os segredos no serviço de hospedagem. Para testes locais sem SMTP, habilite explicitamente `SHOW_CODE_FALLBACK=true`; o código aparece somente na sessão de recuperação e esse recurso é ignorado em produção.

O banco existente continua usando a tabela `users`, seus IDs, papéis e hashes bcrypt (inclusive `$2y$` do PHP). Nenhum dado remoto é migrado automaticamente. `supabase.sql` documenta a criação de um projeto Supabase novo; revise o esquema e as permissões de projetos existentes antes de aplicar qualquer SQL. Scripts MySQL antigos estão na raiz (`schema.sql`, `migrate_company.sql` e `cleanup_painel.sql`) apenas como referência histórica.

Para Google, habilite o provedor no Supabase Auth e permita a URL exata de retorno `APP_URL/api/auth/google/callback` (incluindo o parâmetro de estado dinâmico conforme a configuração de redirect URLs do Supabase). O fluxo usa PKCE e valida estado, identidade Google e confirmação do e-mail. O teste com o provedor real exige configuração externa e uma conta autorizada.

## Funcionalidades

Páginas públicas, cadastro pessoal e de clínica, login com senha/Google, logout, recuperação por código com expiração e limite de tentativas, painel e apresentação do aplicativo. Clínicas podem cadastrar, editar, ativar, desativar e desvincular cuidadores. A API valida a clínica proprietária e revalida status e papel a cada acesso protegido; senhas nunca são devolvidas ao navegador. Sessões usam cookies HttpOnly e SameSite, com proteção CSRF. Links de páginas `.php` são redirecionados para `.html`.

O projeto original não tinha um APK/URL de loja funcional: o botão de download continua apontando para a seção de QR. Isso não produz nem publica um aplicativo mobile.

## Validação

```sh
npm run check
npm test
```

Os testes usam um banco em memória isolado, exercitam HTTP real e um Chromium headless. O teste de navegador usa `CHROMIUM_PATH` se definido, `/usr/bin/chromium` se disponível, ou o Chromium do Playwright (`npx playwright install chromium`). Cobrem cadastro, compatibilidade bcrypt PHP, login/logout, recuperação, isolamento entre clínicas, edição/status/vínculos, menu móvel e acordeões. Não escrevem no Supabase real e não comprovam entrega SMTP nem autenticação Google externa.

## Hospedagem

Publique em um serviço com Node.js; GitHub Pages sozinho não executa a API. Sirva com HTTPS e `NODE_ENV=production`; nessa configuração os cookies exigem HTTPS. O armazenamento de sessão padrão e o limitador são em memória: reiniciar encerra sessões e recuperações pendentes. Para produção com múltiplas instâncias, injete um store persistente compatível com express-session através de `createApp({ sessionStore })` e use um limitador compartilhado. Se o HTTPS terminar em proxy, configure `trust proxy` de forma restrita no servidor para a infraestrutura usada, sem confiar indiscriminadamente em cabeçalhos externos.

## Estrutura

Todos os arquivos do projeto ficam diretamente na raiz:

- `*.html`, `main.css`, `client.js`, `main.js` e imagens: interface pública.
- `index.js`: inicialização do servidor Node.js.
- `server.js`: rotas, validação, autorização e lista explícita dos arquivos públicos.
- `supabase.js`: acesso REST/Auth pelo servidor, respeitando o proxy do ambiente.
- `app.test.js`: testes HTTP e de navegador.
- `supabase.sql`: esquema PostgreSQL; demais arquivos SQL são referências MySQL históricas.

O servidor disponibiliza somente os arquivos públicos autorizados. Código do servidor, testes, SQL, configuração, dependências e documentação não são expostos por HTTP. `node_modules/` é gerada pelo npm e `.git/` contém os metadados do Git; essas pastas de ferramentas não fazem parte da organização do código.
