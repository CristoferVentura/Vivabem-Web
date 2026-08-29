<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check_company.php';
require_once __DIR__ . '/../config/database.php';

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

$emailExists = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
$emailExists->execute(['email' => $email, 'id' => $id]);
if ($emailExists->fetch()) {
    flash('error', 'Este e-mail já está cadastrado.');
    redirect('pages/clinic_caregivers.php?edit=' . $id);
}

$cpfExists = $pdo->prepare('SELECT id FROM users WHERE cpf = :cpf AND id <> :id LIMIT 1');
$cpfExists->execute(['cpf' => $cpf, 'id' => $id]);
if ($cpfExists->fetch()) {
    flash('error', 'Este CPF já está cadastrado.');
    redirect('pages/clinic_caregivers.php?edit=' . $id);
}

if ($password !== '') {
    $update = $pdo->prepare(
        'UPDATE users
         SET name = :name, email = :email, phone = :phone, cpf = :cpf, specialties = :specialties, password = :password
         WHERE id = :id AND clinic_id = :clinic_id
         LIMIT 1'
    );
    $update->execute([
        'name'        => $name,
        'email'       => $email,
        'phone'       => $phone,
        'cpf'         => $cpf,
        'specialties' => $specialties,
        'password'    => password_hash($password, PASSWORD_DEFAULT),
        'id'          => $id,
        'clinic_id'   => current_user_id(),
    ]);
} else {
    $update = $pdo->prepare(
        'UPDATE users
         SET name = :name, email = :email, phone = :phone, cpf = :cpf, specialties = :specialties
         WHERE id = :id AND clinic_id = :clinic_id
         LIMIT 1'
    );
    $update->execute([
        'name'        => $name,
        'email'       => $email,
        'phone'       => $phone,
        'cpf'         => $cpf,
        'specialties' => $specialties,
        'id'          => $id,
        'clinic_id'   => current_user_id(),
    ]);
}

flash('success', 'Dados do cuidador atualizados.');
redirect('pages/clinic_caregivers.php');
