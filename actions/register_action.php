<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}

$name     = trim((string) ($_POST['name'] ?? ''));
$email    = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirm  = (string) ($_POST['password_confirm'] ?? '');

if ($name === '' || $email === '' || $password === '') {
    flash('error', 'Preencha todos os campos obrigatórios.');
    redirect('register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Informe um e-mail válido.');
    redirect('register.php');
}

if (mb_strlen($password) < 6) {
    flash('error', 'A senha deve ter pelo menos 6 caracteres.');
    redirect('register.php');
}

if ($password !== $confirm) {
    flash('error', 'As senhas não coincidem.');
    redirect('register.php');
}

try {
    $existingUser = supabase_first(supabase_request('users', 'GET', null, [
        'select' => 'id',
        'email'  => 'eq.' . $email,
        'limit'  => 1,
    ]));

    if ($existingUser) {
        flash('error', 'Este e-mail já está cadastrado.');
        redirect('register.php');
    }

    supabase_request('users', 'POST', [
        'name'     => $name,
        'email'    => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role'     => 'family',
        'status'   => 'active',
    ]);
} catch (SupabaseException $e) {
    error_log('VivaBem Supabase register: ' . $e->getMessage());
    flash('error', 'Não foi possível criar a conta agora. Tente novamente.');
    redirect('register.php');
}

flash('success', 'Conta criada com sucesso. Faça login para continuar.');
redirect('login.php');
