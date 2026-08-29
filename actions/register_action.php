<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

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

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    flash('error', 'Este e-mail já está cadastrado.');
    redirect('register.php');
}

$insert = $pdo->prepare(
    'INSERT INTO users (name, email, password, role, status) VALUES (:name, :email, :password, :role, :status)'
);
$insert->execute([
    'name'     => $name,
    'email'    => $email,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'role'     => 'family',
    'status'   => 'active',
]);

flash('success', 'Conta criada com sucesso. Faça login para continuar.');
redirect('login.php');
