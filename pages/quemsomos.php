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

    <section class="section pillars-section" data-pillars-section>
        <div class="pillars-section__checkpoint" data-pillars-checkpoint aria-hidden="true"></div>
        <div class="pillars-section__pin" data-pillars-pin>
            <div class="section-inner pillars-section__inner">
                <h2 class="section-title center" data-pillars-title>Nossos pilares</h2>
                <div class="pillars pillars--about">
                <article class="pillar pillar--about" data-pillar-reveal>
                    <div class="pillar__icon" aria-hidden="true">
                        <span class="pillar__emoji">🎯</span>
                    </div>
                    <div class="pillar__body">
                        <h3>1. Missão</h3>
                        <p>Prover uma plataforma acessível e completa para cuidadores autônomos, idosos e suas famílias, oferecendo praticidade na organização e no monitoramento contínuo da rotina. O projeto busca zelar pelo bem-estar da terceira idade ao simplificar o acompanhamento e garantir suporte no cuidado diário.</p>
                    </div>
                </article>
                <article class="pillar pillar--about" data-pillar-reveal>
                    <div class="pillar__icon" aria-hidden="true">
                        <span class="pillar__emoji">👁️</span>
                    </div>
                    <div class="pillar__body">
                        <h3>2. Visão</h3>
                        <p>Ser referência no suporte ao cuidado com idosos, conectando famílias, cuidadores e clínicas por meio de uma plataforma prática e em constante evolução. Almeja-se expandir a atuação para ambientes clínicos especializados, tornando a busca por cuidadores e a gestão da saúde do idoso mais ágeis, seguras e acessíveis.</p>
                    </div>
                </article>
                <article class="pillar pillar--about" data-pillar-reveal>
                    <div class="pillar__icon" aria-hidden="true">
                        <span class="pillar__emoji">💎</span>
                    </div>
                    <div class="pillar__body">
                        <h3>3. Valores</h3>
                        <p>Guiamos cada decisão pela acessibilidade digital, proteção rigorosa dos dados, empatia no cuidado com a terceira idade, integração entre família, cuidador e idoso, e ferramentas práticas que simplificam a rotina e tornam o acompanhamento mais seguro e eficiente para todos.</p>
                    </div>
                </article>
            </div>
            </div>
        </div>
    </section>
</main>

    <section class="team-sectors" id="desenvolvedores">
        <div class="section-inner team-sectors__inner">
            <h2 class="team-sectors__title" data-reveal>Os desenvolvedores</h2>
            <p class="team-sectors__lead" data-reveal>Conheça os setores que constroem o VivaBem. Clique em cada área para expandir ou recolher.</p>

            <?php
            $sectors = [
                [
                    'title' => 'Front-end (Mobile & Web)',
                    'flow' => 'normal',
                    'description' => 'Responsável pelas interfaces intuitivas do app mobile (focado em acessibilidade para idosos e cuidadores) e do painel web. Utiliza React Native (EAS Build, Google Maps API) e plataforma Web para gestão centralizada.',
                    'members' => [
                        ['name' => 'Cristofer', 'role' => 'Liderança estratégica (CEO) e refinamento das telas mobile.'],
                        ['name' => 'Nathan', 'role' => 'Construção do painel Web e desenvolvimento mobile.'],
                    ],
                ],
                [
                    'title' => 'Back-end',
                    'flow' => 'reverse',
                    'description' => 'Garante a lógica de negócios, rotas, autenticação e comunicação segura entre plataformas. Estruturado em APIs PHP e microsserviços no Supabase para funcionalidades em tempo real (como o chat).',
                    'members' => [
                        ['name' => 'Nathan', 'role' => 'Servidor, regras de negócio e integração de APIs.'],
                        ['name' => 'Gustavo', 'role' => 'Suporte à estrutura de servidor e ponte com a base de dados.'],
                    ],
                ],
                [
                    'title' => 'Banco de Dados',
                    'flow' => 'normal',
                    'description' => 'Cuida da modelagem, integridade e segurança de dados sensíveis sobre a saúde dos idosos. Estruturado no Supabase (PostgreSQL) e MySQL/XAMPP para sincronização de perfis e mensagens em tempo real.',
                    'members' => [
                        ['name' => 'Gustavo', 'role' => 'Criação de tabelas, relacionamentos e gestão da base de dados.'],
                    ],
                ],
                [
                    'title' => 'Design (UI/UX) & Qualidade',
                    'flow' => 'reverse',
                    'description' => 'Focado na identidade visual acessível, prototipagem, usabilidade e validação de componentes. Realiza testes funcionais (QA) para garantia de qualidade e correção de bugs.',
                    'members' => [
                        ['name' => 'Wellington', 'role' => 'Criação de layouts e testes funcionais no aplicativo.'],
                        ['name' => 'Gabriel', 'role' => 'Construção e validação de elementos de design e identidade visual.'],
                    ],
                ],
                [
                    'title' => 'Documentação do Projeto',
                    'flow' => 'normal',
                    'description' => 'Responsável por registrar o desenvolvimento técnico e acadêmico do TCC, organizando requisitos, diagramas de arquitetura, manuais do sistema e o relatório final.',
                    'members' => [
                        ['name' => 'João', 'role' => 'Elaboração e estruturação da documentação técnica e apoio no setor Web.'],
                        ['name' => 'Gabriel', 'role' => 'Suporte à redação e organização documental.'],
                    ],
                ],
            ];

            foreach ($sectors as $index => $sector):
                $sectorNumber = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                $panelId = 'team-sector-panel-' . ($index + 1);
                $flowClass = ($sector['flow'] ?? 'normal') === 'reverse'
                    ? 'team-sector__layout--reverse'
                    : 'team-sector__layout--normal';
            ?>
                <article class="team-sector" data-team-sector>
                    <button
                        class="team-sector__header"
                        type="button"
                        aria-expanded="false"
                        aria-controls="<?= e($panelId) ?>"
                    >
                        <span class="team-sector__number"><?= e($sectorNumber) ?></span>
                        <span class="team-sector__heading"><?= e($sector['title']) ?></span>
                        <span class="team-sector__chevron" aria-hidden="true"></span>
                    </button>

                    <div class="team-sector__panel" id="<?= e($panelId) ?>">
                        <div class="team-sector__panel-inner">
                            <div class="team-sector__layout <?= e($flowClass) ?>">
                                <div class="team-sector__col team-sector__col--title">
                                    <span class="team-sector__label">Setor</span>
                                    <div class="team-sector__title-wrap">
                                        <h3><?= e($sector['title']) ?></h3>
                                    </div>
                                </div>

                                <div class="team-sector__col team-sector__col--description">
                                    <span class="team-sector__label">Sobre o setor</span>
                                    <div class="team-sector__description-scroll">
                                        <p><?= e($sector['description']) ?></p>
                                    </div>
                                </div>

                                <div class="team-sector__col team-sector__col--members">
                                    <span class="team-sector__label">Integrantes</span>
                                    <div class="team-sector__members">
                                        <?php foreach ($sector['members'] as $member):
                                            $initial = mb_substr($member['name'], 0, 1);
                                        ?>
                                            <article class="team-sector__member">
                                                <div class="team-sector__avatar"><?= e($initial) ?></div>
                                                <div>
                                                    <h4><?= e($member['name']) ?></h4>
                                                    <p><?= e($member['role']) ?></p>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
