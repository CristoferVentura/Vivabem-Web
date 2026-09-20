<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register_company.php');
}

$companyName = trim((string) ($_POST['company_name'] ?? ''));
$legalName   = trim((string) ($_POST['legal_name'] ?? ''));
$cnpj        = only_digits((string) ($_POST['cnpj'] ?? ''));
$email       = trim((string) ($_POST['email'] ?? ''));
$phone       = trim((string) ($_POST['phone'] ?? ''));
$contactName = trim((string) ($_POST['contact_name'] ?? ''));
$password    = (string) ($_POST['password'] ?? '');
$confirm     = (string) ($_POST['password_confirm'] ?? '');

if ($companyName === '' || $legalName === '' || $cnpj === '' || $email === '' || $phone === '' || $contactName === '' || $password === '') {
    flash('error', 'Preencha todos os campos obrigatórios.');
    redirect('register_company.php');
}

if (strlen($cnpj) !== 14) {
    flash('error', 'Informe um CNPJ válido com 14 dígitos.');
    redirect('register_company.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Informe um e-mail corporativo válido.');
    redirect('register_company.php');
}

if (mb_strlen($password) < 6) {
    flash('error', 'A senha deve ter pelo menos 6 caracteres.');
    redirect('register_company.php');
}

if ($password !== $confirm) {
    flash('error', 'As senhas não coincidem.');
    redirect('register_company.php');
}

try {
    $existingUser = supabase_first(supabase_request('users', 'GET', null, [
        'select' => 'id',
        'email'  => 'eq.' . $email,
        'limit'  => 1,
    ]));

    if ($existingUser) {
        flash('error', 'Este e-mail já está cadastrado.');
        redirect('register_company.php');
    }

    $existingCnpj = supabase_first(supabase_request('users', 'GET', null, [
        'select' => 'id',
        'cnpj'   => 'eq.' . $cnpj,
        'limit'  => 1,
    ]));

    if ($existingCnpj) {
        flash('error', 'Este CNPJ já está cadastrado.');
        redirect('register_company.php');
    }

    supabase_request('users', 'POST', [
        'name'         => $companyName,
        'email'        => $email,
        'password'     => password_hash($password, PASSWORD_DEFAULT),
        'role'         => 'company',
        'status'       => 'active',
        'company_name' => $companyName,
        'legal_name'   => $legalName,
        'cnpj'         => $cnpj,
        'phone'        => $phone,
        'contact_name' => $contactName,
    ]);
} catch (SupabaseException $e) {
    error_log('VivaBem Supabase register company: ' . $e->getMessage());
    flash('error', 'Não foi possível cadastrar a clínica agora. Tente novamente.');
    redirect('register_company.php');
}

flash('success', 'Clínica cadastrada com sucesso. Faça login para acessar o painel.');
redirect('login.php');
