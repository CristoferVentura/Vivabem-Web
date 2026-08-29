<?php

declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/mailer.php';

if (is_logged_in()) {
    redirect('pages/dashboard.php');
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

$maskedEmail = mask_email((string) $pending['email']);
$previewToken = $_SESSION['pending_token_preview'] ?? null;

$pageTitle = 'Verificar código — VivaBem';
$activeNav = 'login';
$bodyClass = 'page-auth';
require __DIR__ . '/includes/header.php';
?>

<main class="auth-page">
    <div class="auth-card">
        <h1>Verificar código</h1>
        <p>Enviamos um código de 6 dígitos para <strong><?= e($maskedEmail) ?></strong>. Digite-o abaixo para redefinir sua senha.</p>

        <?php if ($previewToken): ?>
            <div class="token-preview" role="status">
                <span>Modo local/teste — seu código:</span>
                <strong><?= e((string) $previewToken) ?></strong>
            </div>
        <?php endif; ?>

        <form action="<?= e(base_url('actions/verify_action.php')) ?>" method="POST" data-validate>
            <div class="form-group">
                <label for="token">Código de verificação</label>
                <input
                    type="text"
                    id="token"
                    name="token"
                    class="token-input"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    minlength="6"
                    required
                    autocomplete="one-time-code"
                    placeholder="000000"
                >
            </div>
            <button type="submit" class="btn btn-primary btn-block">Confirmar código</button>
        </form>

        <form action="<?= e(base_url('actions/resend_token_action.php')) ?>" method="POST" class="resend-form">
            <button type="submit" class="btn btn-secondary btn-block">Reenviar código</button>
        </form>

        <p class="auth-alt">
            E-mail errado?
            <a href="<?= e(base_url('actions/cancel_register_action.php')) ?>">Voltar à recuperação</a>
        </p>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
