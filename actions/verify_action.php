<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('verify.php');
}

$pending = pending_registration();
if ($pending === null) {
    flash('error', 'Nenhum cadastro pendente. Preencha o formulário novamente.');
    redirect('register.php');
}

if (time() > (int) $pending['expires_at']) {
    clear_pending_registration();
    flash('error', 'O código expirou. Cadastre-se novamente.');
    redirect('register.php');
}

$token = (string) preg_replace('/\D+/', '', (string) ($_POST['token'] ?? ''));

if (strlen($token) !== 6) {
    flash('error', 'Digite o código de 6 dígitos enviado ao seu e-mail.');
    redirect('verify.php');
}

$attempts = (int) ($pending['attempts'] ?? 0) + 1;
$_SESSION['pending_registration']['attempts'] = $attempts;

if ($attempts > 5) {
    clear_pending_registration();
    flash('error', 'Muitas tentativas inválidas. Reinicie o cadastro.');
    redirect('register.php');
}

$expected = (string) $pending['token_hash'];
$given    = hash('sha256', $token);

if (!hash_equals($expected, $given)) {
    flash('error', 'Código inválido. Verifique o e-mail e tente novamente.');
    redirect('verify.php');
}

// Garante que o e-mail não foi cadastrado enquanto a verificação estava aberta
$check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$check->execute(['email' => $pending['email']]);
if ($check->fetch()) {
    clear_pending_registration();
    flash('error', 'Este e-mail já está cadastrado. Faça login.');
    redirect('login.php');
}

$insert = $pdo->prepare(
    'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
);
$insert->execute([
    'name'     => $pending['name'],
    'email'    => $pending['email'],
    'password' => $pending['password'],
]);

session_regenerate_id(true);

$_SESSION['user_id']    = (int) $pdo->lastInsertId();
$_SESSION['user_name']  = $pending['name'];
$_SESSION['user_email'] = $pending['email'];

clear_pending_registration();

flash('success', 'E-mail confirmado! Bem-vindo ao VivaBem.');
redirect('pages/dashboard.php');
