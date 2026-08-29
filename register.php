<?php
require_once __DIR__ . '/config/app.php';

if (is_logged_in()) {
    redirect('pages/dashboard.php');
}

$pageTitle = 'Cadastrar — VivaBem';
$activeNav = 'register';
$bodyClass = 'page-auth';
require __DIR__ . '/includes/header.php';
?>

<main class="auth-page">
    <div class="auth-card">
        <h1>Criar conta</h1>
        <p>Crie sua conta para acessar o painel e o ecossistema VivaBem. Você já poderá entrar em seguida.</p>

        <form action="<?= e(base_url('actions/register_action.php')) ?>" method="POST" data-validate>
            <div class="form-group">
                <label for="name">Nome completo</label>
                <input type="text" id="name" name="name" required autocomplete="name" placeholder="Seu nome">
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="seu@email.com">
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres">
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmar senha</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6" autocomplete="new-password" placeholder="Repita a senha">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Criar conta</button>
        </form>

        <p class="auth-alt">Sou clínica ou empresa? <a href="<?= e(base_url('register_company.php')) ?>">Cadastrar clínica</a></p>
        <p class="auth-alt">Já tem conta? <a href="<?= e(base_url('login.php')) ?>">Entrar</a></p>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
