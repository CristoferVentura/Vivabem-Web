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

$find = $pdo->prepare(
    'SELECT id, status FROM users WHERE id = :id AND role = :role AND clinic_id = :clinic_id LIMIT 1'
);
$find->execute([
    'id'        => $id,
    'role'      => 'caregiver',
    'clinic_id' => current_user_id(),
]);
$caregiver = $find->fetch();

if (!$caregiver) {
    flash('error', 'Cuidador não encontrado nesta clínica.');
    redirect('pages/clinic_caregivers.php');
}

$next = (($caregiver['status'] ?? '') === 'active') ? 'inactive' : 'active';

$update = $pdo->prepare(
    'UPDATE users SET status = :status WHERE id = :id AND clinic_id = :clinic_id LIMIT 1'
);
$update->execute([
    'status'    => $next,
    'id'        => $id,
    'clinic_id' => current_user_id(),
]);

flash('success', $next === 'active' ? 'Cuidador ativado.' : 'Cuidador desativado.');
redirect('pages/clinic_caregivers.php');
