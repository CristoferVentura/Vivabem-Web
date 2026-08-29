<?php
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Painel — VivaBem';
$activeNav = 'dashboard';
require __DIR__ . '/../includes/header.php';

$qrPath = ROOT_PATH . '/assets/images/qr-download.png';
$hasQr  = is_file($qrPath);
// Altere o link abaixo quando o APK / loja estiver disponível
$downloadUrl = '#download-app';
?>


<main class="dashboard">
    <div class="dash-head" data-animate="hero">
        <p class="badge"><?= is_company() ? 'Área da clínica' : 'Área do usuário' ?></p>
        <h1>Olá, <?= e(current_user_first_name()) ?></h1>
        <p>Acesse o ecossistema mobile do VivaBem e acompanhe o cuidado em tempo real.</p>
        <?php if (is_company()): ?>
            <div class="dash-head__actions">
                <a href="<?= e(base_url('pages/clinic_caregivers.php')) ?>" class="btn btn-primary">Gerenciar cuidadores</a>
            </div>
        <?php endif; ?>
    </div>

    <section class="app-showcase" data-reveal>
        <div class="app-showcase__glow" aria-hidden="true"></div>

        <div class="app-showcase__grid">
            <div class="app-showcase__copy">
                <span class="badge">Aplicativo Mobile</span>
                <h2>VivaBem na palma da mão</h2>
                <p class="app-showcase__lead">
                    O aplicativo VivaBem possui uma interface integrada criada para conectar
                    cuidadores e familiares, facilitando o compartilhamento de informações em tempo real.
                </p>

                <div class="app-showcase__actions">
                    <a href="<?= e($downloadUrl) ?>" class="btn btn-primary" id="download-app">
                        Download do App
                    </a>
                    <a href="<?= e(base_url('pages/nossoApp.php')) ?>" class="btn btn-secondary">
                        Ver mais sobre o app
                    </a>
                </div>

                <div class="app-qr" id="qr-download">
                    <div class="app-qr__frame">
                        <?php if ($hasQr): ?>
                            <img src="<?= e(asset('images/qr-download.png')) ?>" alt="QR Code para download do aplicativo VivaBem">
                        <?php else: ?>
                            <div class="app-qr__placeholder">
                                <span class="app-qr__mark">QR</span>
                                <p>Espaço reservado para o QR Code de download</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="app-qr__meta">
                        <strong>Escaneie para baixar</strong>
                        <span>Disponível para Android. Aponte a câmera do celular para o código.</span>
                    </div>
                </div>
            </div>

            <aside class="app-showcase__roles">
                <article class="role-card role-card--care">
                    <div class="role-card__head">
                        <span class="icon-mark">CO</span>
                        <h3>Para o Cuidador</h3>
                    </div>
                    <ul>
                        <li>Criação de relatórios diários de acompanhamento.</li>
                        <li>Controle de medicação (remédios ministrados, pendentes e horários).</li>
                        <li>Registro e marcação de atividades diárias realizadas pelo idoso.</li>
                    </ul>
                </article>

                <article class="role-card role-card--family">
                    <div class="role-card__head">
                        <span class="icon-mark">FA</span>
                        <h3>Para o Familiar</h3>
                    </div>
                    <ul>
                        <li>Adição e gerenciamento de cuidadores vinculados.</li>
                        <li>Busca de novos cuidadores disponíveis na região.</li>
                        <li>Cadastro e perfil dos idosos.</li>
                        <li>Feed de atualizações em tempo real com relatos dos cuidadores.</li>
                    </ul>
                </article>
            </aside>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
