<?php
/**
 * Middleware de autenticação — páginas restritas.
 * Inclua no topo de dashboard.php, nossoApp.php etc.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

if (empty($_SESSION['user_id'])) {
    flash('error', 'Faça login para acessar esta área.');
    redirect('login.php');
}
