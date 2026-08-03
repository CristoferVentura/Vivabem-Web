<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('verify.php');
}

$pending = pending_registration();
if ($pending === null) {
    flash('error', 'Nenhum cadastro pendente. Preencha o formulário novamente.');
    redirect('register.php');
}

// Evita spam: no máximo 1 reenvio a cada 45 segundos
$last = (int) ($pending['created_at'] ?? 0);
if (time() - $last < 45) {
    flash('error', 'Aguarde alguns segundos antes de solicitar um novo código.');
    redirect('verify.php');
}

$token = create_email_verification_token(
    (string) $pending['name'],
    (string) $pending['email'],
    (string) $pending['password']
);

$sent = send_verification_email((string) $pending['email'], (string) $pending['name'], $token);
$mailConfig = require __DIR__ . '/../config/mail.php';

if (!$sent || trim((string) ($mailConfig['smtp_user'] ?? '')) === '') {
    if (!empty($mailConfig['show_code_fallback'])) {
        $_SESSION['pending_token_preview'] = $token;
    }
}

if ($sent && trim((string) ($mailConfig['smtp_user'] ?? '')) !== '') {
    unset($_SESSION['pending_token_preview']);
    flash('success', 'Novo código enviado para o seu e-mail.');
} elseif (!empty($mailConfig['show_code_fallback'])) {
    flash('success', 'Novo código gerado (modo local/teste).');
} else {
    flash('error', 'Falha ao reenviar o e-mail. Configure o SMTP em config/mail.php.');
}

redirect('verify.php');
