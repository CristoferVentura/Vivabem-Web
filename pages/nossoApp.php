<?php
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Sobre o App — VivaBem';
$activeNav = 'app';
require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
    <div data-animate="hero">
        <h1>Inovação mobile para a <span>terceira idade</span></h1>
        <p>Conheça as tecnologias e recursos que transformam o smartphone em aliado da saúde diária.</p>
    </div>
</section>

<main>
    <section class="section">
        <div class="section-inner app-grid-2">
            <div class="app-image-container" data-reveal>
                <div class="app-mockup" data-parallax>
                    <div class="slider-container">
                        <img class="slide active" src="<?= e(asset('images/print-apps_idoso1.jpeg')) ?>" alt="Print do aplicativo VivaBem 1">
                        <img class="slide" src="<?= e(asset('images/print-apps_idoso2.jpeg')) ?>" alt="Print do aplicativo VivaBem 2">
                    </div>
                </div>
            </div>
            <div data-reveal>
                <span class="badge">Tecnologia acessível</span>
                <h2 class="section-title">Projetado onde a acessibilidade é prioridade</h2>
                <p class="section-lead">O aplicativo <strong>VivaBem</strong> foi concebido sob critérios de UX para a terceira idade: fontes ampliadas, botões de alto contraste e navegação livre de menus confusos.</p>
                <p>A plataforma web atua junto ao app mobile, permitindo que cuidadores e familiares acompanhem dados registrados com mais tranquilidade.</p>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="section-inner">
            <h2 class="section-title center" data-reveal>O que o nosso ecossistema faz?</h2>
            <p class="section-lead center" data-reveal>Soluções focadas na prevenção de riscos e no acompanhamento simplificado.</p>
            <div class="features">
                <article class="feature-card" data-reveal>
                    <div class="icon-mark">AL</div>
                    <h3>Alertas inteligentes</h3>
                    <p>Cronogramas de medicamentos com avisos que ajudam a evitar o esquecimento de doses essenciais.</p>
                </article>
                <article class="feature-card" data-reveal>
                    <div class="icon-mark">SOS</div>
                    <h3>Botão de SOS</h3>
                    <p>Comando rápido para acionar a rede de apoio com localização e contato imediato da família.</p>
                </article>
                <article class="feature-card" data-reveal>
                    <div class="icon-mark">CE</div>
                    <h3>Conexão com familiares</h3>
                    <p>Conexão com familiares para receber notificações e acompanhar o bem-estar do idoso.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
