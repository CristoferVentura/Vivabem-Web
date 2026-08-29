<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$email    = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    flash('error', 'Informe e-mail e senha.');
    redirect('login.php');
}

$stmt = $pdo->prepare(
    'SELECT id, name, email, password, role, status FROM users WHERE email = :email LIMIT 1'
);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    flash('error', 'E-mail ou senha incorretos.');
    redirect('login.php');
}

if (($user['status'] ?? 'active') !== 'active') {
    flash('error', 'Esta conta está desativada. Fale com a clínica responsável.');
    redirect('login.php');
}

session_regenerate_id(true);

$_SESSION['user_id']    = (int) $user['id'];
$_SESSION['user_name']  = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role']  = (string) ($user['role'] ?? 'family');

flash('success', 'Login realizado com sucesso.');
redirect('pages/dashboard.php');
