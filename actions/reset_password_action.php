<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('reset_password.php');
}

$pending = pending_password_reset();
if ($pending === null || empty($pending['verified']) || (int) ($pending['user_id'] ?? 0) < 1) {
    flash('error', 'Confirme o código de recuperação antes de redefinir a senha.');
    redirect('forgot_password.php');
}

$password = (string) ($_POST['password'] ?? '');
$confirm  = (string) ($_POST['password_confirm'] ?? '');

if (mb_strlen($password) < 6) {
    flash('error', 'A senha deve ter pelo menos 6 caracteres.');
    redirect('reset_password.php');
}

if ($password !== $confirm) {
    flash('error', 'As senhas não coincidem.');
    redirect('reset_password.php');
}

try {
    supabase_request('users', 'PATCH', [
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ], [
        'id' => 'eq.' . (int) $pending['user_id'],
    ]);
} catch (SupabaseException $e) {
    error_log('VivaBem Supabase reset password: ' . $e->getMessage());
    flash('error', 'Não foi possível atualizar a senha agora. Tente novamente.');
    redirect('reset_password.php');
}

clear_pending_password_reset();

flash('success', 'Senha atualizada. Faça login com a nova senha.');
redirect('login.php');
