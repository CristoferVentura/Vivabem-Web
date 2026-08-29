<?php
require_once __DIR__ . '/config/app.php';

if (is_logged_in()) {
    redirect('pages/dashboard.php');
}

$pageTitle = 'Cadastrar clínica — VivaBem';
$activeNav = 'register';
$bodyClass = 'page-auth';
require __DIR__ . '/includes/header.php';
?>

<main class="auth-page">
    <div class="auth-card auth-card--wide">
        <h1>Cadastro de clínica</h1>
        <p>Para clínicas, casas de repouso ou agências de cuidadores. Após o cadastro você já pode entrar no painel.</p>

        <form action="<?= e(base_url('actions/register_company_action.php')) ?>" method="POST" data-validate>
            <div class="form-grid">
                <div class="form-group">
                    <label for="company_name">Nome fantasia</label>
                    <input type="text" id="company_name" name="company_name" required maxlength="180" autocomplete="organization" placeholder="Nome da clínica">
                </div>
                <div class="form-group">
                    <label for="legal_name">Razão social</label>
                    <input type="text" id="legal_name" name="legal_name" required maxlength="180" autocomplete="organization" placeholder="Razão social">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="cnpj">CNPJ</label>
                    <input type="text" id="cnpj" name="cnpj" required inputmode="numeric" data-mask="cnpj" maxlength="18" autocomplete="off" placeholder="00.000.000/0000-00">
                </div>
                <div class="form-group">
                    <label for="email">E-mail corporativo</label>
                    <input type="email" id="email" name="email" required autocomplete="email" placeholder="contato@clinica.com">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="phone">Telefone / WhatsApp</label>
                    <input type="tel" id="phone" name="phone" required data-mask="phone" maxlength="16" autocomplete="tel" placeholder="(00) 00000-0000">
                </div>
                <div class="form-group">
                    <label for="contact_name">Responsável técnico ou contato</label>
                    <input type="text" id="contact_name" name="contact_name" required maxlength="120" autocomplete="name" placeholder="Nome do responsável">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirmar senha</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6" autocomplete="new-password" placeholder="Repita a senha">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Criar conta da clínica</button>
        </form>

        <p class="auth-alt">Cadastro familiar? <a href="<?= e(base_url('register.php')) ?>">Criar conta pessoal</a></p>
        <p class="auth-alt">Já tem conta? <a href="<?= e(base_url('login.php')) ?>">Entrar</a></p>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
