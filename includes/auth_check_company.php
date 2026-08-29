<?php
/**
 * Middleware — páginas restritas a clínicas / empresas (role company).
 */

declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';

if (!is_company()) {
    flash('error', 'Acesso restrito a clínicas e empresas.');
    redirect('pages/dashboard.php');
}
