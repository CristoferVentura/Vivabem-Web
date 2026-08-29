<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('verify.php');
}

$pending = pending_password_reset();
if ($pending === null) {
    flash('error', 'Solicite um código de recuperação de senha.');
    redirect('forgot_password.php');
}

if (time() > (int) $pending['expires_at']) {
    clear_pending_password_reset();
    flash('error', 'O código expirou. Solicite uma nova recuperação de senha.');
    redirect('forgot_password.php');
}

$token = (string) preg_replace('/\D+/', '', (string) ($_POST['token'] ?? ''));

if (strlen($token) !== 6) {
    flash('error', 'Digite o código de 6 dígitos enviado ao seu e-mail.');
    redirect('verify.php');
}

$attempts = (int) ($pending['attempts'] ?? 0) + 1;
$_SESSION['pending_password_reset']['attempts'] = $attempts;

if ($attempts > 5) {
    clear_pending_password_reset();
    flash('error', 'Muitas tentativas inválidas. Solicite a recuperação novamente.');
    redirect('forgot_password.php');
}

$expected = (string) $pending['token_hash'];
$given    = hash('sha256', $token);

if (!hash_equals($expected, $given) || (int) ($pending['user_id'] ?? 0) < 1) {
    flash('error', 'Código inválido. Verifique o e-mail e tente novamente.');
    redirect('verify.php');
}

$_SESSION['pending_password_reset']['verified'] = true;

flash('success', 'Código confirmado. Defina sua nova senha.');
redirect('reset_password.php');
