<?php
require_once __DIR__ . '/config/app.php';

if (is_logged_in()) {
    redirect('pages/dashboard.php');
}

$pageTitle = 'Entrar — VivaBem';
$activeNav = 'login';
$bodyClass = 'page-auth';
require __DIR__ . '/includes/header.php';
?>

<main class="auth-page">
    <div class="auth-card">
        <h1>Entrar</h1>
        <p>Acesse seu painel de cuidados VivaBem.</p>

        <form action="<?= e(base_url('actions/login_action.php')) ?>" method="POST" data-validate>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="seu@email.com">
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>

        <p class="auth-alt"><a href="<?= e(base_url('forgot_password.php')) ?>">Esqueci minha senha</a></p>
        <p class="auth-alt">Ainda não tem conta? <a href="<?= e(base_url('register.php')) ?>">Cadastre-se</a></p>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
