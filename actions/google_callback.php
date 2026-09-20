<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/supabase.php';

function google_login_fail(string $message): never
{
    unset($_SESSION['google_oauth_verifier'], $_SESSION['google_oauth_state']);
    flash('error', $message);
    redirect('login.php');
}

if (is_logged_in()) {
    redirect('pages/dashboard.php');
}

$error = trim((string) ($_GET['error'] ?? $_GET['error_code'] ?? ''));
if ($error !== '') {
    $description = trim((string) ($_GET['error_description'] ?? ''));
    error_log('VivaBem Google OAuth: ' . $error . ' ' . $description);
    google_login_fail('Não foi possível entrar com o Google. Confira se o provedor está ativo no Supabase.');
}

$state = (string) ($_GET['state'] ?? '');
$expectedState = (string) ($_SESSION['google_oauth_state'] ?? '');
$verifier = (string) ($_SESSION['google_oauth_verifier'] ?? '');
$code = trim((string) ($_GET['code'] ?? ''));

if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
    google_login_fail('A sessão do login Google expirou. Tente novamente.');
}

if ($code === '' || $verifier === '') {
    google_login_fail('Não recebemos a confirmação do Google. Tente entrar novamente.');
}

$user = null;

try {
    $session = supabase_exchange_pkce($code, $verifier);
    $authUser = $session['user'];

    $email = strtolower(trim((string) ($authUser['email'] ?? '')));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        google_login_fail('O Google não enviou um e-mail válido para esta conta.');
    }

    $meta = is_array($authUser['user_metadata'] ?? null) ? $authUser['user_metadata'] : [];
    $name = trim((string) ($meta['full_name'] ?? $meta['name'] ?? ''));
    if ($name === '') {
        $name = (string) (explode('@', $email)[0] ?? 'Usuário VivaBem');
    }

    $googleId = '';
    foreach (($authUser['identities'] ?? []) as $identity) {
        if (!is_array($identity) || ($identity['provider'] ?? '') !== 'google') {
            continue;
        }

        $googleId = trim((string) ($identity['id'] ?? ''));
        break;
    }

    if ($googleId === '') {
        $googleId = trim((string) ($authUser['id'] ?? ''));
    }

    $user = null;

    if ($googleId !== '') {
        $user = supabase_first(supabase_request('users', 'GET', null, [
            'select'    => 'id,name,email,role,status,auth_provider,google_id',
            'google_id' => 'eq.' . $googleId,
            'limit'     => 1,
        ]));
    }

    if (!$user) {
        $user = supabase_first(supabase_request('users', 'GET', null, [
            'select' => 'id,name,email,role,status,auth_provider,google_id',
            'email'  => 'eq.' . $email,
            'limit'  => 1,
        ]));
    }

    if (!$user) {
        $created = supabase_request('users', 'POST', [
            'name'          => $name,
            'email'         => $email,
            'password'      => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
            'role'          => 'family',
            'status'        => 'active',
            'auth_provider' => 'google',
            'google_id'     => $googleId !== '' ? $googleId : null,
        ]);

        $user = supabase_first($created);
        if (!$user && is_array($created) && isset($created['id'])) {
            $user = $created;
        }
    } else {
        $patch = [];

        if ($googleId !== '' && trim((string) ($user['google_id'] ?? '')) === '') {
            $patch['google_id'] = $googleId;
        }

        if ($patch !== []) {
            supabase_request('users', 'PATCH', $patch, [
                'id' => 'eq.' . $user['id'],
            ]);
            $user = array_merge($user, $patch);
        }
    }
} catch (SupabaseException $e) {
    error_log('VivaBem Google callback: ' . $e->getMessage());
    google_login_fail('Não foi possível concluir o login com o Google. Tente novamente.');
}

unset($_SESSION['google_oauth_verifier'], $_SESSION['google_oauth_state']);

if (!$user) {
    google_login_fail('Não foi possível localizar ou criar sua conta VivaBem.');
}

if (($user['status'] ?? 'active') !== 'active') {
    flash('error', 'Esta conta está desativada. Fale com a clínica responsável.');
    redirect('login.php');
}

login_user($user);
flash('success', 'Login com Google realizado com sucesso.');
redirect('pages/dashboard.php');
