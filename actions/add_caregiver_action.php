<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check_company.php';
require_once __DIR__ . '/../config/database.php';

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

$emailExists = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$emailExists->execute(['email' => $email]);
if ($emailExists->fetch()) {
    flash('error', 'Este e-mail já está cadastrado.');
    redirect('pages/clinic_caregivers.php');
}

$cpfExists = $pdo->prepare('SELECT id FROM users WHERE cpf = :cpf LIMIT 1');
$cpfExists->execute(['cpf' => $cpf]);
if ($cpfExists->fetch()) {
    flash('error', 'Este CPF já está cadastrado.');
    redirect('pages/clinic_caregivers.php');
}

$insert = $pdo->prepare(
    'INSERT INTO users
        (name, email, password, role, status, phone, cpf, specialties, clinic_id)
     VALUES
        (:name, :email, :password, :role, :status, :phone, :cpf, :specialties, :clinic_id)'
);
$insert->execute([
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

flash('success', 'Cuidador cadastrado e vinculado à clínica.');
redirect('pages/clinic_caregivers.php');
