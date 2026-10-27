import { EnvHttpProxyAgent, fetch } from 'undici';

// Respect the cloud environment's proxy and TLS trust configuration.
const dispatcher = new EnvHttpProxyAgent();
export function createSupabase({ url, key }) {
  return async (path, { method = 'GET', data, params = {}, auth = false } = {}) => {
    if (!url || !key) throw Object.assign(new Error('Configure SUPABASE_URL e SUPABASE_KEY no servidor.'), { status: 503 });
    const target = new URL(`${url.replace(/\/$/, '')}/${auth ? 'auth' : 'rest'}/v1/${path}`);
    target.search = new URLSearchParams(params).toString();
    let response;
    try {
      response = await fetch(target, {
        dispatcher, method, signal: AbortSignal.timeout(15000),
        headers: { apikey: key, Authorization: `Bearer ${key}`, 'Content-Type': 'application/json', Prefer: 'return=representation' },
        body: data === undefined ? undefined : JSON.stringify(data),
      });
    } catch { throw Object.assign(new Error('Não foi possível conectar ao Supabase.'), { status: 502 }); }
    if (!response.ok) {
      // Never expose backend responses, keys or password records to the client/logs.
      throw Object.assign(new Error(response.status === 409 ? 'E-mail, CPF ou CNPJ já cadastrado.' : 'Não foi possível concluir a operação no banco de dados.'), { status: response.status === 409 ? 409 : 502 });
    }
    return response.status === 204 ? null : response.json();
  };
}
