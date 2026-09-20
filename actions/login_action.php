<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$email    = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    flash('error', 'Informe e-mail e senha.');
    redirect('login.php');
}

try {
    $user = supabase_first(supabase_request('users', 'GET', null, [
        'select' => 'id,name,email,password,role,status,auth_provider',
        'email'  => 'eq.' . $email,
        'limit'  => 1,
    ]));
} catch (SupabaseException $e) {
    error_log('VivaBem Supabase login: ' . $e->getMessage());
    flash('error', 'Não foi possível validar o login agora. Tente novamente.');
    redirect('login.php');
}

if (!$user || !password_verify($password, $user['password'])) {
    if ($user && ($user['auth_provider'] ?? '') === 'google') {
        flash('error', 'Esta conta entra com o Google. Use o botão Continuar com o Google.');
    } else {
        flash('error', 'E-mail ou senha incorretos.');
    }
    redirect('login.php');
}

if (($user['status'] ?? 'active') !== 'active') {
    flash('error', 'Esta conta está desativada. Fale com a clínica responsável.');
    redirect('login.php');
}

login_user($user);

flash('success', 'Login realizado com sucesso.');
redirect('pages/dashboard.php');
