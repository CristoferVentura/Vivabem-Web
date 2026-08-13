<?php
$pageTitle = 'VivaBem';
$activeNav = 'home';
$bodyClass = 'page-home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-container" data-animate="hero">
        <p class="hero-brand">Viva<span>Bem</span></p>
        <h1>A solução inteligente para o dia a dia, agora na <em>sua mão</em></h1>
        <p>A tecnologia pensada para cuidar da terceira idade com acessibilidade, segurança familiar e tecnologia acolhedora.</p>
        <div class="hero-actions">
            <?php if (is_logged_in()): ?>
                <a href="<?= e(base_url('pages/dashboard.php')) ?>" class="btn btn-primary">Abrir painel</a>
                <a href="<?= e(base_url('pages/nossoApp.php')) ?>" class="btn btn-secondary">Conhecer o app</a>
            <?php else: ?>
                <a href="<?= e(base_url('register.php')) ?>" class="btn btn-primary">Começar agora</a>
                <a href="<?= e(base_url('pages/quemsomos.php')) ?>" class="btn btn-secondary">Quem somos</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<main>
    <section class="section">
        <div class="section-inner about-grid">
            <div data-reveal>
                <span class="badge">Conscientização</span>
                <h2 class="section-title">A importância do cuidado com os idosos</h2>
                <p class="section-lead">Os idosos representam a base da nossa sociedade, guardando histórias, conhecimentos e experiências valiosas. Garantir um envelhecimento digno e seguro é uma responsabilidade coletiva.</p>
                <p>O cuidado humanizado vai além da assistência básica: envolve saúde mental, autonomia e tecnologia para facilitar o monitoramento da rotina diária.</p>
            </div>
            <div class="media-panel" data-reveal>
                Cuidado com presença,<br>tecnologia com empatia
            </div>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="section-inner about-grid">
            <div class="media-panel" data-reveal>
                Movimento, equilíbrio<br>e bem-estar contínuo
            </div>
            <div data-reveal>
                <h2 class="section-title">Qualidade de vida e movimento</h2>
                <p class="section-lead">A prática regular de atividades físicas adaptadas é um dos pilares da longevidade saudável.</p>
                <p>Exercícios leves a moderados ajudam a manter a mobilidade, fortalecem a musculatura e melhoram o equilíbrio — além de reduzir isolamento e ansiedade.</p>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
