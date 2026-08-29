<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check_company.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pages/clinic_caregivers.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id < 1) {
    flash('error', 'Cuidador inválido.');
    redirect('pages/clinic_caregivers.php');
}

$owned = $pdo->prepare(
    'SELECT id FROM users WHERE id = :id AND role = :role AND clinic_id = :clinic_id LIMIT 1'
);
$owned->execute([
    'id'        => $id,
    'role'      => 'caregiver',
    'clinic_id' => current_user_id(),
]);

if (!$owned->fetch()) {
    flash('error', 'Cuidador não encontrado nesta clínica.');
    redirect('pages/clinic_caregivers.php');
}

$unlink = $pdo->prepare(
    'UPDATE users SET clinic_id = NULL WHERE id = :id AND clinic_id = :clinic_id LIMIT 1'
);
$unlink->execute([
    'id'        => $id,
    'clinic_id' => current_user_id(),
]);

flash('success', 'Vínculo do cuidador removido.');
redirect('pages/clinic_caregivers.php');
