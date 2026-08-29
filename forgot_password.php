<?php
require_once __DIR__ . '/config/app.php';

if (is_logged_in()) {
    redirect('pages/dashboard.php');
}

$pageTitle = 'Esqueci minha senha — VivaBem';
$activeNav = 'login';
$bodyClass = 'page-auth';
require __DIR__ . '/includes/header.php';
?>

<main class="auth-page">
    <div class="auth-card">
        <h1>Esqueci minha senha</h1>
        <p>Informe o e-mail da sua conta. Se ele estiver cadastrado, enviaremos um código de verificação.</p>

        <form action="<?= e(base_url('actions/forgot_password_action.php')) ?>" method="POST" data-validate>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="seu@email.com">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Enviar código</button>
        </form>

        <p class="auth-alt"><a href="<?= e(base_url('login.php')) ?>">Voltar ao login</a></p>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
