<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('forgot_password.php');
}

$email = trim((string) ($_POST['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Informe um e-mail válido.');
    redirect('forgot_password.php');
}

try {
    $user = supabase_first(supabase_request('users', 'GET', null, [
        'select' => 'id,name,email',
        'email'  => 'eq.' . $email,
        'limit'  => 1,
    ]));
} catch (SupabaseException $e) {
    error_log('VivaBem Supabase forgot password: ' . $e->getMessage());
    flash('error', 'Não foi possível iniciar a recuperação agora. Tente novamente.');
    redirect('forgot_password.php');
}

$mailConfig = require __DIR__ . '/../config/mail.php';
$showFallback = !empty($mailConfig['show_code_fallback']);
$hasSmtpUser = trim((string) ($mailConfig['smtp_user'] ?? '')) !== '';

if ($user) {
    $token = create_password_reset_token((int) $user['id'], (string) $user['name'], (string) $user['email']);
    $sent  = send_verification_email((string) $user['email'], (string) $user['name'], $token);

    if (!$sent || ((string) ($mailConfig['driver'] ?? '') === 'smtp' && !$hasSmtpUser)) {
        if ($showFallback) {
            $_SESSION['pending_token_preview'] = $token;
        }
    }

    if ($sent && $hasSmtpUser) {
        unset($_SESSION['pending_token_preview']);
    } elseif (!$sent && !$showFallback) {
        clear_pending_password_reset();
        flash('error', 'Não foi possível enviar o e-mail. Configure o SMTP em config/mail.php.');
        redirect('forgot_password.php');
    }
} else {
    create_password_reset_token(0, 'Usuário', $email);
    unset($_SESSION['pending_token_preview']);
}

flash('success', 'Se este e-mail estiver cadastrado, enviamos um código de verificação.');
redirect('verify.php');
