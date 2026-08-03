<?php
require_once __DIR__ . '/../config/app.php';

$pageTitle = $pageTitle ?? 'VivaBem';
$activeNav = $activeNav ?? '';
$flash = get_flash();
$loggedIn = is_logged_in();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
    <?php if (!empty($extraCss)): ?>
        <link rel="stylesheet" href="<?= e(asset('css/' . $extraCss)) ?>">
    <?php endif; ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
    <header class="site-header" data-animate="header">
        <div class="nav-container">
            <a href="<?= e(base_url('index.php')) ?>" class="brand-container">
                <img src="<?= e(asset('images/VivaBem.png')) ?>" alt="Logo VivaBem" class="logo-img">
                <span class="logo-text">Viva<span>Bem</span></span>
            </a>

            <button class="nav-toggle" type="button" aria-label="Abrir menu" aria-expanded="false">
                <span></span><span></span>
            </button>

            <ul class="nav-menu">
                <li><a href="<?= e(base_url('index.php')) ?>" class="<?= $activeNav === 'home' ? 'active' : '' ?>">Home</a></li>
                <li><a href="<?= e(base_url('pages/quemsomos.php')) ?>" class="<?= $activeNav === 'quemsomos' ? 'active' : '' ?>">Quem Somos</a></li>
                <?php if ($loggedIn): ?>
                    <li><a href="<?= e(base_url('pages/nossoApp.php')) ?>" class="<?= $activeNav === 'app' ? 'active' : '' ?>">Sobre o App</a></li>
                    <li><a href="<?= e(base_url('pages/dashboard.php')) ?>" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">Painel</a></li>
                    <li><a href="<?= e(base_url('logout.php')) ?>" class="nav-cta nav-cta--ghost">Sair</a></li>
                <?php else: ?>
                    <li><a href="<?= e(base_url('login.php')) ?>" class="<?= $activeNav === 'login' ? 'active' : '' ?>">Entrar</a></li>
                    <li><a href="<?= e(base_url('register.php')) ?>" class="nav-cta">Cadastrar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="flash flash--<?= e($flash['type']) ?>" role="alert" data-animate="flash">
            <div class="flash-inner">
                <span><?= e($flash['message']) ?></span>
                <button type="button" class="flash-close" aria-label="Fechar">&times;</button>
            </div>
        </div>
    <?php endif; ?>
