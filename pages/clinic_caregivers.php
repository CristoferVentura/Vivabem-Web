<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check_company.php';
require_once __DIR__ . '/../config/database.php';

$clinicId = current_user_id();
$editing  = null;
$editId   = (int) ($_GET['edit'] ?? 0);

if ($editId > 0) {
    $find = $pdo->prepare(
        'SELECT id, name, email, phone, cpf, specialties, status
         FROM users
         WHERE id = :id AND role = :role AND clinic_id = :clinic_id
         LIMIT 1'
    );
    $find->execute([
        'id'        => $editId,
        'role'      => 'caregiver',
        'clinic_id' => $clinicId,
    ]);
    $editing = $find->fetch() ?: null;

    if ($editing === null) {
        flash('error', 'Cuidador não encontrado nesta clínica.');
        redirect('pages/clinic_caregivers.php');
    }
}

$list = $pdo->prepare(
    'SELECT id, name, email, phone, cpf, specialties, status, created_at
     FROM users
     WHERE role = :role AND clinic_id = :clinic_id
     ORDER BY name ASC'
);
$list->execute([
    'role'      => 'caregiver',
    'clinic_id' => $clinicId,
]);
$caregivers = $list->fetchAll();

$pageTitle = 'Cuidadores — VivaBem';
$activeNav = 'caregivers';
require __DIR__ . '/../includes/header.php';
?>

<main class="dashboard">
    <div class="dash-head" data-animate="hero">
        <p class="badge">Painel da clínica</p>
        <h1><?= $editing ? 'Editar cuidador' : 'Cuidadores da equipe' ?></h1>
        <p>Cadastre, edite e gerencie os cuidadores vinculados à sua clínica.</p>
    </div>

    <section class="clinic-panel" data-reveal>
        <h2><?= $editing ? 'Atualizar dados' : 'Cadastrar cuidador' ?></h2>

        <form
            action="<?= e(base_url($editing ? 'actions/update_caregiver_action.php' : 'actions/add_caregiver_action.php')) ?>"
            method="POST"
            data-validate
        >
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nome</label>
                    <input type="text" id="name" name="name" required maxlength="120" autocomplete="name" placeholder="Nome completo" value="<?= e((string) ($editing['name'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" required inputmode="numeric" data-mask="cpf" maxlength="14" autocomplete="off" placeholder="000.000.000-00" value="<?= e($editing ? format_cpf((string) $editing['cpf']) : '') ?>">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required autocomplete="email" placeholder="cuidador@email.com" value="<?= e((string) ($editing['email'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Telefone</label>
                    <input type="tel" id="phone" name="phone" required data-mask="phone" maxlength="16" autocomplete="tel" placeholder="(00) 00000-0000" value="<?= e((string) ($editing['phone'] ?? '')) ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="specialties">Especialidades</label>
                <input type="text" id="specialties" name="specialties" required maxlength="255" placeholder="Ex.: geriatria, Alzheimer, fisioterapia" value="<?= e((string) ($editing['specialties'] ?? '')) ?>">
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="password"><?= $editing ? 'Nova senha (opcional)' : 'Senha inicial' ?></label>
                    <input type="password" id="password" name="password" <?= $editing ? '' : 'required' ?> minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label for="password_confirm"><?= $editing ? 'Confirmar nova senha' : 'Confirmar senha' ?></label>
                    <input type="password" id="password_confirm" name="password_confirm" <?= $editing ? '' : 'required' ?> minlength="6" autocomplete="new-password" placeholder="Repita a senha">
                </div>
            </div>

            <div class="clinic-panel__actions">
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Salvar alterações' : 'Cadastrar cuidador' ?></button>
                <?php if ($editing): ?>
                    <a class="btn btn-secondary" href="<?= e(base_url('pages/clinic_caregivers.php')) ?>">Cancelar edição</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="clinic-panel" data-reveal>
        <h2>Cuidadores vinculados</h2>

        <?php if ($caregivers === []): ?>
            <p class="clinic-panel__empty">Nenhum cuidador cadastrado ainda.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Especialidades</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($caregivers as $caregiver): ?>
                            <?php $isActive = ($caregiver['status'] ?? '') === 'active'; ?>
                            <tr>
                                <td><?= e((string) $caregiver['name']) ?></td>
                                <td><?= e(format_cpf((string) ($caregiver['cpf'] ?? ''))) ?></td>
                                <td><?= e((string) $caregiver['email']) ?></td>
                                <td><?= e((string) ($caregiver['phone'] ?? '')) ?></td>
                                <td><?= e((string) ($caregiver['specialties'] ?? '')) ?></td>
                                <td>
                                    <span class="status-badge status-badge--<?= $isActive ? 'active' : 'inactive' ?>">
                                        <?= $isActive ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a class="btn btn-secondary btn-sm" href="<?= e(base_url('pages/clinic_caregivers.php?edit=' . (int) $caregiver['id'])) ?>">Editar</a>
                                        <form action="<?= e(base_url('actions/toggle_caregiver_action.php')) ?>" method="POST">
                                            <input type="hidden" name="id" value="<?= (int) $caregiver['id'] ?>">
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <?= $isActive ? 'Desativar' : 'Ativar' ?>
                                            </button>
                                        </form>
                                        <form action="<?= e(base_url('actions/unlink_caregiver_action.php')) ?>" method="POST" onsubmit="return confirm('Remover o vínculo deste cuidador com a clínica?');">
                                            <input type="hidden" name="id" value="<?= (int) $caregiver['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Remover vínculo</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
