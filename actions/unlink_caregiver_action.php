<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check_company.php';
require_once __DIR__ . '/../config/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pages/clinic_caregivers.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id < 1) {
    flash('error', 'Cuidador inválido.');
    redirect('pages/clinic_caregivers.php');
}

try {
    $owned = supabase_first(supabase_request('users', 'GET', null, [
        'select'    => 'id',
        'id'        => 'eq.' . $id,
        'role'      => 'eq.caregiver',
        'clinic_id' => 'eq.' . current_user_id(),
        'limit'     => 1,
    ]));

    if (!$owned) {
        flash('error', 'Cuidador não encontrado nesta clínica.');
        redirect('pages/clinic_caregivers.php');
    }

    supabase_request('users', 'PATCH', [
        'clinic_id' => null,
    ], [
        'id'        => 'eq.' . $id,
        'clinic_id' => 'eq.' . current_user_id(),
    ]);
} catch (SupabaseException $e) {
    error_log('VivaBem Supabase unlink caregiver: ' . $e->getMessage());
    flash('error', 'Não foi possível remover o vínculo do cuidador agora.');
    redirect('pages/clinic_caregivers.php');
}

flash('success', 'Vínculo do cuidador removido.');
redirect('pages/clinic_caregivers.php');
