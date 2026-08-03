<?php
$pageTitle = 'Quem Somos — VivaBem';
$activeNav = 'quemsomos';
require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
    <div data-animate="hero">
        <h1>Nossa missão é <span>Viva Bem</span></h1>
        <p>Conheça a história e a equipe que uniu tecnologia e empatia para transformar a saúde na terceira idade.</p>
    </div>
</section>

<main>
    <section class="section">
        <div class="section-inner qs-grid-2">
            <div data-reveal>
                <span class="badge">Quem Somos</span>
                <h2 class="section-title">Tecnologia voltada para o cuidado</h2>
                <p class="section-lead">O projeto <strong>VivaBem</strong> nasceu nas salas da Etec, motivado pela necessidade de apoiar avós e pais idosos em uma rotina saudável e segura.</p>
                <p>Percebemos que o mercado mobile está cheio de apps complexos, com letras pequenas e interfaces confusas. Decidimos mudar esse cenário com uma ferramenta inclusiva, focada em acessibilidade e monitoramento preventivo.</p>
            </div>
            <div class="media-panel" data-reveal>
                Inclusão digital<br>com olhar humano
            </div>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="section-inner">
            <h2 class="section-title center" data-reveal>Nossos pilares</h2>
            <div class="pillars">
                <article class="pillar" data-reveal>
                    <div class="icon-mark">ID</div>
                    <h3>Inclusão Digital</h3>
                    <p>Tecnologia que o idoso consegue usar sozinho, devolvendo autonomia no dia a dia.</p>
                </article>
                <article class="pillar" data-reveal>
                    <div class="icon-mark">SF</div>
                    <h3>Segurança Familiar</h3>
                    <p>Tranquilidade para cuidadores e parentes com alertas rápidos e monitoramento constante.</p>
                </article>
                <article class="pillar" data-reveal>
                    <div class="icon-mark">EV</div>
                    <h3>Engenharia de Valor</h3>
                    <p>Práticas modernas de código mobile e web para entregar um produto robusto.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="section-inner">
            <h2 class="section-title center" data-reveal>Os desenvolvedores</h2>
            <div class="team-grid">
                <?php
                $team = [
                    ['Cristofer', 'Líder do Projeto / Front-end', 'Arquitetura web e identidade visual da marca VivaBem.'],
                    ['Gustavo', 'Desenvolvedor Mobile', 'Aplicativo e integração dos sensores de monitoramento.'],
                    ['Nathan', 'Dev Full-Stack', 'API RESTful, regras de negócio e integração do banco de dados.'],
                    ['Gabriel', 'Testes / Documentação', 'Testes de qualidade e elaboração da documentação do projeto.'],
                    ['João', 'Front-end / Documentação', 'Apoio nas telas do sistema e documentação técnica.'],
                    ['Wellyngton', 'Back-end / Banco de Dados', 'API, segurança das informações e servidores do ecossistema.'],
                ];
                foreach ($team as $member):
                    $initial = mb_substr($member[0], 0, 1);
                ?>
                    <article class="team-card" data-reveal>
                        <div class="avatar"><?= e($initial) ?></div>
                        <h3><?= e($member[0]) ?></h3>
                        <p class="role"><?= e($member[1]) ?></p>
                        <p><?= e($member[2]) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
