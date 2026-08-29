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

$last = (int) ($pending['created_at'] ?? 0);
if (time() - $last < 45) {
    flash('error', 'Aguarde alguns segundos antes de solicitar um novo código.');
    redirect('verify.php');
}

$userId = (int) ($pending['user_id'] ?? 0);
$token = create_password_reset_token(
    $userId,
    (string) $pending['name'],
    (string) $pending['email']
);

$mailConfig = require __DIR__ . '/../config/mail.php';
$showFallback = !empty($mailConfig['show_code_fallback']);
$hasSmtpUser = trim((string) ($mailConfig['smtp_user'] ?? '')) !== '';

if ($userId > 0) {
    $sent = send_verification_email((string) $pending['email'], (string) $pending['name'], $token);

    if (!$sent || ((string) ($mailConfig['driver'] ?? '') === 'smtp' && !$hasSmtpUser)) {
        if ($showFallback) {
            $_SESSION['pending_token_preview'] = $token;
        }
    }

    if ($sent && $hasSmtpUser) {
        unset($_SESSION['pending_token_preview']);
        flash('success', 'Novo código enviado para o seu e-mail.');
    } elseif ($showFallback) {
        flash('success', 'Novo código gerado (modo local/teste).');
    } else {
        flash('error', 'Falha ao reenviar o e-mail. Configure o SMTP em config/mail.php.');
    }
} else {
    unset($_SESSION['pending_token_preview']);
    flash('success', 'Se este e-mail estiver cadastrado, enviamos um novo código.');
}

redirect('verify.php');
