<?php

declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/mailer.php';

if (is_logged_in()) {
    redirect('pages/dashboard.php');
}

$pending = pending_password_reset();
if ($pending === null || empty($pending['verified']) || (int) ($pending['user_id'] ?? 0) < 1) {
    flash('error', 'Confirme o código de recuperação antes de redefinir a senha.');
    redirect('forgot_password.php');
}

$pageTitle = 'Nova senha — VivaBem';
$activeNav = 'login';
$bodyClass = 'page-auth';
require __DIR__ . '/includes/header.php';
?>

<main class="auth-page">
    <div class="auth-card">
        <h1>Nova senha</h1>
        <p>Defina uma nova senha para a conta <strong><?= e(mask_email((string) $pending['email'])) ?></strong>.</p>

        <form action="<?= e(base_url('actions/reset_password_action.php')) ?>" method="POST" data-validate>
            <div class="form-group">
                <label for="password">Nova senha</label>
                <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres">
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmar senha</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6" autocomplete="new-password" placeholder="Repita a senha">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Salvar nova senha</button>
        </form>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
