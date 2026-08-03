<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

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

$hash  = password_hash($password, PASSWORD_DEFAULT);
$token = create_email_verification_token($name, $email, $hash);
$sent  = send_verification_email($email, $name, $token);

$mailConfig = require __DIR__ . '/../config/mail.php';
$showFallback = !empty($mailConfig['show_code_fallback']);

if (!$sent || ($mailConfig['driver'] === 'smtp' && trim((string) $mailConfig['smtp_user']) === '')) {
    // Modo local / falha de envio: permite testar sem SMTP configurado
    if ($showFallback) {
        $_SESSION['pending_token_preview'] = $token;
    }
}

if ($sent && trim((string) ($mailConfig['smtp_user'] ?? '')) !== '') {
    unset($_SESSION['pending_token_preview']);
    flash('success', 'Enviamos um código de verificação para o seu e-mail.');
} elseif ($showFallback) {
    flash('success', 'Código gerado. Confirme o token na próxima tela (modo local/teste).');
} else {
    flash('error', 'Não foi possível enviar o e-mail. Configure o SMTP em config/mail.php.');
    clear_pending_registration();
    redirect('register.php');
}

redirect('verify.php');
