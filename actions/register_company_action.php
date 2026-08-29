<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

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

$exists = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$exists->execute(['email' => $email]);
if ($exists->fetch()) {
    flash('error', 'Este e-mail já está cadastrado.');
    redirect('register_company.php');
}

$cnpjExists = $pdo->prepare('SELECT id FROM users WHERE cnpj = :cnpj LIMIT 1');
$cnpjExists->execute(['cnpj' => $cnpj]);
if ($cnpjExists->fetch()) {
    flash('error', 'Este CNPJ já está cadastrado.');
    redirect('register_company.php');
}

$insert = $pdo->prepare(
    'INSERT INTO users
        (name, email, password, role, status, company_name, legal_name, cnpj, phone, contact_name)
     VALUES
        (:name, :email, :password, :role, :status, :company_name, :legal_name, :cnpj, :phone, :contact_name)'
);
$insert->execute([
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

flash('success', 'Clínica cadastrada com sucesso. Faça login para acessar o painel.');
redirect('login.php');
