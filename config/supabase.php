<?php
/**
 * Cliente REST Supabase — VivaBem
 *
 * Configure preferencialmente por variáveis de ambiente:
 * SUPABASE_URL=https://seu-projeto.supabase.co
 * SUPABASE_KEY=sua_anon_ou_service_role_key
 *
 * Em backend PHP, a service_role pode ser usada apenas se este arquivo nunca for
 * exposto ao navegador ou versionado com a chave real.
 */

declare(strict_types=1);

define('SUPABASE_URL', rtrim((string) (getenv('SUPABASE_URL') ?: 'https://doenvfnvlnmcdmorxhpt.supabase.co'), '/'));
define('SUPABASE_KEY', (string) (getenv('SUPABASE_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRvZW52Zm52bG5tY2Rtb3J4aHB0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODM4NzQzODcsImV4cCI6MjA5OTQ1MDM4N30.uNYyKC-FIO9l15d8Y8MJlbrGysWkGDJJOoxRWMhcw7s'));

class SupabaseException extends RuntimeException
{
}

/**
 * Executa uma chamada HTTP na API PostgREST do Supabase.
 *
 * Exemplos:
 * supabase_request('tarefas?select=*', 'GET');
 * supabase_request('tarefas', 'POST', $dados);
 * supabase_request('tarefas?id=eq.1', 'PATCH', $dados);
 * supabase_request('tarefas?id=eq.1', 'DELETE');
 *
 * @param array<string, mixed>|null $data
 * @param array<string, mixed> $params
 * @return mixed
 */
function supabase_request(string $endpoint, string $method = 'GET', ?array $data = null, array $params = [])
{
    if (SUPABASE_URL === 'https://SEU-PROJETO.supabase.co' || SUPABASE_KEY === 'COLE_SUA_SUPABASE_API_KEY_AQUI') {
        throw new SupabaseException('Configure SUPABASE_URL e SUPABASE_KEY em config/supabase.php ou nas variáveis de ambiente.');
    }

    $method = strtoupper($method);
    $url = supabase_build_url($endpoint, $params);

    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation',
    ];

    $ch = curl_init($url);

    if ($ch === false) {
        throw new SupabaseException('Não foi possível iniciar a requisição cURL.');
    }

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);

    if ($data !== null && in_array($method, ['POST', 'PATCH', 'PUT'], true)) {
        try {
            $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            curl_close($ch);
            throw new SupabaseException('Erro ao converter dados para JSON: ' . $e->getMessage());
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        throw new SupabaseException('Erro cURL ao conectar ao Supabase: ' . $curlError);
    }

    $decoded = $response === '' ? null : json_decode((string) $response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new SupabaseException('Resposta inválida do Supabase: ' . json_last_error_msg());
    }

    if ($statusCode >= 400) {
        $message = is_array($decoded)
            ? (string) ($decoded['message'] ?? $decoded['hint'] ?? $decoded['details'] ?? 'Erro na API do Supabase.')
            : 'Erro na API do Supabase.';

        throw new SupabaseException("Supabase HTTP {$statusCode}: {$message}");
    }

    return $decoded;
}

/**
 * Retorna o primeiro registro de uma resposta de listagem.
 *
 * @param array<int, mixed>|mixed $rows
 * @return mixed|null
 */
function supabase_first($rows)
{
    return is_array($rows) && array_key_exists(0, $rows) ? $rows[0] : null;
}

/**
 * Monta a URL final aceitando endpoint com query string e parâmetros extras.
 *
 * @param array<string, mixed> $params
 */
function supabase_build_url(string $endpoint, array $params = []): string
{
    $endpoint = ltrim($endpoint, '/');
    $query = '';

    if (str_contains($endpoint, '?')) {
        [$endpoint, $query] = explode('?', $endpoint, 2);
    }

    $extraQuery = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

    if ($query !== '' && $extraQuery !== '') {
        $query .= '&' . $extraQuery;
    } elseif ($extraQuery !== '') {
        $query = $extraQuery;
    }

    return SUPABASE_URL . '/rest/v1/' . $endpoint . ($query !== '' ? '?' . $query : '');
}

/**
 * Chamada HTTP à API de Auth do Supabase (/auth/v1).
 *
 * @param array<string, mixed>|null $data
 * @param array<string, mixed> $query
 * @return mixed
 */
function supabase_auth_request(string $path, string $method = 'POST', ?array $data = null, array $query = [])
{
    $method = strtoupper($method);
    $url = SUPABASE_URL . '/auth/v1/' . ltrim($path, '/');

    if ($query !== []) {
        $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
    ];

    $ch = curl_init($url);

    if ($ch === false) {
        throw new SupabaseException('Não foi possível iniciar a requisição cURL de autenticação.');
    }

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);

    if ($data !== null && in_array($method, ['POST', 'PATCH', 'PUT'], true)) {
        try {
            $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            curl_close($ch);
            throw new SupabaseException('Erro ao converter dados de autenticação para JSON: ' . $e->getMessage());
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        throw new SupabaseException('Erro cURL ao autenticar no Supabase: ' . $curlError);
    }

    $decoded = $response === '' ? null : json_decode((string) $response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new SupabaseException('Resposta inválida da autenticação do Supabase: ' . json_last_error_msg());
    }

    if ($statusCode >= 400) {
        $message = is_array($decoded)
            ? (string) ($decoded['error_description'] ?? $decoded['msg'] ?? $decoded['error'] ?? $decoded['message'] ?? 'Erro na autenticação do Supabase.')
            : 'Erro na autenticação do Supabase.';

        throw new SupabaseException("Supabase Auth HTTP {$statusCode}: {$message}");
    }

    return $decoded;
}

function supabase_pkce_verifier(): string
{
    return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
}

function supabase_pkce_challenge(string $verifier): string
{
    return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
}

function supabase_google_authorize_url(string $redirectTo, string $codeChallenge): string
{
    return SUPABASE_URL . '/auth/v1/authorize?' . http_build_query([
        'provider'               => 'google',
        'redirect_to'            => $redirectTo,
        'scopes'                 => 'openid email profile',
        'code_challenge'         => $codeChallenge,
        'code_challenge_method'  => 's256',
    ], '', '&', PHP_QUERY_RFC3986);
}

/**
 * Troca o código PKCE por uma sessão do Supabase Auth.
 *
 * @return array<string, mixed>
 */
function supabase_exchange_pkce(string $authCode, string $codeVerifier): array
{
    $session = supabase_auth_request('token', 'POST', [
        'auth_code'     => $authCode,
        'code_verifier' => $codeVerifier,
    ], [
        'grant_type' => 'pkce',
    ]);

    if (!is_array($session) || empty($session['user']) || !is_array($session['user'])) {
        throw new SupabaseException('A sessão do Google não retornou os dados do usuário.');
    }

    return $session;
}
