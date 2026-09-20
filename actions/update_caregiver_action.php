<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check_company.php';
require_once __DIR__ . '/../config/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pages/clinic_caregivers.php');
}

$id          = (int) ($_POST['id'] ?? 0);
$name        = trim((string) ($_POST['name'] ?? ''));
$cpf         = only_digits((string) ($_POST['cpf'] ?? ''));
$email       = trim((string) ($_POST['email'] ?? ''));
$phone       = trim((string) ($_POST['phone'] ?? ''));
$specialties = trim((string) ($_POST['specialties'] ?? ''));
$password    = (string) ($_POST['password'] ?? '');
$confirm     = (string) ($_POST['password_confirm'] ?? '');

if ($id < 1) {
    flash('error', 'Cuidador inválido.');
    redirect('pages/clinic_caregivers.php');
}

if ($name === '' || $cpf === '' || $email === '' || $phone === '' || $specialties === '') {
    flash('error', 'Preencha todos os campos obrigatórios.');
    redirect('pages/clinic_caregivers.php?edit=' . $id);
}

if (strlen($cpf) !== 11) {
    flash('error', 'Informe um CPF válido com 11 dígitos.');
    redirect('pages/clinic_caregivers.php?edit=' . $id);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Informe um e-mail válido.');
    redirect('pages/clinic_caregivers.php?edit=' . $id);
}

if ($password !== '' || $confirm !== '') {
    if (mb_strlen($password) < 6) {
        flash('error', 'A senha deve ter pelo menos 6 caracteres.');
        redirect('pages/clinic_caregivers.php?edit=' . $id);
    }

    if ($password !== $confirm) {
        flash('error', 'As senhas não coincidem.');
        redirect('pages/clinic_caregivers.php?edit=' . $id);
    }
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

    $emailExists = supabase_first(supabase_request('users', 'GET', null, [
        'select' => 'id',
        'email'  => 'eq.' . $email,
        'id'     => 'neq.' . $id,
        'limit'  => 1,
    ]));

    if ($emailExists) {
        flash('error', 'Este e-mail já está cadastrado.');
        redirect('pages/clinic_caregivers.php?edit=' . $id);
    }

    $cpfExists = supabase_first(supabase_request('users', 'GET', null, [
        'select' => 'id',
        'cpf'    => 'eq.' . $cpf,
        'id'     => 'neq.' . $id,
        'limit'  => 1,
    ]));

    if ($cpfExists) {
        flash('error', 'Este CPF já está cadastrado.');
        redirect('pages/clinic_caregivers.php?edit=' . $id);
    }

    $data = [
        'name'        => $name,
        'email'       => $email,
        'phone'       => $phone,
        'cpf'         => $cpf,
        'specialties' => $specialties,
    ];

    if ($password !== '') {
        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    supabase_request('users', 'PATCH', $data, [
        'id'        => 'eq.' . $id,
        'clinic_id' => 'eq.' . current_user_id(),
    ]);
} catch (SupabaseException $e) {
    error_log('VivaBem Supabase update caregiver: ' . $e->getMessage());
    flash('error', 'Não foi possível atualizar o cuidador agora. Tente novamente.');
    redirect('pages/clinic_caregivers.php?edit=' . $id);
}

flash('success', 'Dados do cuidador atualizados.');
redirect('pages/clinic_caregivers.php');
