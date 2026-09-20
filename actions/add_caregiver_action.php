<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check_company.php';
require_once __DIR__ . '/../config/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pages/clinic_caregivers.php');
}

$name        = trim((string) ($_POST['name'] ?? ''));
$cpf         = only_digits((string) ($_POST['cpf'] ?? ''));
$email       = trim((string) ($_POST['email'] ?? ''));
$phone       = trim((string) ($_POST['phone'] ?? ''));
$specialties = trim((string) ($_POST['specialties'] ?? ''));
$password    = (string) ($_POST['password'] ?? '');
$confirm     = (string) ($_POST['password_confirm'] ?? '');

if ($name === '' || $cpf === '' || $email === '' || $phone === '' || $specialties === '' || $password === '') {
    flash('error', 'Preencha todos os campos obrigatórios.');
    redirect('pages/clinic_caregivers.php');
}

if (strlen($cpf) !== 11) {
    flash('error', 'Informe um CPF válido com 11 dígitos.');
    redirect('pages/clinic_caregivers.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Informe um e-mail válido.');
    redirect('pages/clinic_caregivers.php');
}

if (mb_strlen($password) < 6) {
    flash('error', 'A senha deve ter pelo menos 6 caracteres.');
    redirect('pages/clinic_caregivers.php');
}

if ($password !== $confirm) {
    flash('error', 'As senhas não coincidem.');
    redirect('pages/clinic_caregivers.php');
}

try {
    $emailExists = supabase_first(supabase_request('users', 'GET', null, [
        'select' => 'id',
        'email'  => 'eq.' . $email,
        'limit'  => 1,
    ]));

    if ($emailExists) {
        flash('error', 'Este e-mail já está cadastrado.');
        redirect('pages/clinic_caregivers.php');
    }

    $cpfExists = supabase_first(supabase_request('users', 'GET', null, [
        'select' => 'id',
        'cpf'    => 'eq.' . $cpf,
        'limit'  => 1,
    ]));

    if ($cpfExists) {
        flash('error', 'Este CPF já está cadastrado.');
        redirect('pages/clinic_caregivers.php');
    }

    supabase_request('users', 'POST', [
        'name'        => $name,
        'email'       => $email,
        'password'    => password_hash($password, PASSWORD_DEFAULT),
        'role'        => 'caregiver',
        'status'      => 'active',
        'phone'       => $phone,
        'cpf'         => $cpf,
        'specialties' => $specialties,
        'clinic_id'   => current_user_id(),
    ]);
} catch (SupabaseException $e) {
    error_log('VivaBem Supabase add caregiver: ' . $e->getMessage());
    flash('error', 'Não foi possível cadastrar o cuidador agora. Tente novamente.');
    redirect('pages/clinic_caregivers.php');
}

flash('success', 'Cuidador cadastrado e vinculado à clínica.');
redirect('pages/clinic_caregivers.php');
