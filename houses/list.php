<?php
/**
 * E-Condo Packages - Listar Casas
 */

require_once '../config/config.php';

// Verificar autenticação e permissão
if (!isLoggedIn()) {
    redirect('/login.php');
}

if (!hasRole(['admin'])) {
    setFlash('danger', 'Você não tem permissão para acessar esta página.');
    redirect('/index.php');
}

$houseModel = new House();
$villageModel = new Village();

$villageId = $_GET['village_id'] ?? '';

if ($villageId) {
    $houses = $houseModel->getByVillage($villageId);
    $village = $villageModel->findById($villageId);
} else {
    $houses = $houseModel->getAllWithVillageInfo();
    $village = null;
}

$villages = $villageModel->getActive();

$pageTitle = 'Casas';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-house"></i> Casas
                    <?php if ($village): ?>
                        - <?= htmlspecialchars($village['name']) ?>
                    <?php endif; ?>
                </h1>
                <div>
                    <?php if ($village): ?>
                        <a href="<?= SITE_URL ?>/villages/list.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    <?php endif; ?>
                    <a href="<?= SITE_URL ?>/houses/create.php<?= $villageId ? '?village_id=' . $villageId : '' ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nova Casa
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$villageId): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="village_id" class="form-label">Filtrar por Village</label>
                            <select class="form-select" id="village_id" name="village_id" onchange="this.form.submit()">
                                <option value="">Todas as Villages</option>
                                <?php foreach ($villages as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= $villageId == $v['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($v['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list"></i> 
                        <?= count($houses) ?> casa(s) cadastrada(s)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($houses)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Nenhuma casa cadastrada ainda.
                            <a href="<?= SITE_URL ?>/houses/create.php<?= $villageId ? '?village_id=' . $villageId : '' ?>" class="alert-link">Cadastrar primeira casa</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <?php if (!$villageId): ?>
                                        <th>Village</th>
                                        <?php endif; ?>
                                        <th>Número da Casa</th>
                                        <th>Complemento</th>
                                        <th>Condôminos</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($houses as $house): ?>
                                    <tr>
                                        <td><?= $house['id'] ?></td>
                                        <?php if (!$villageId): ?>
                                        <td><?= htmlspecialchars($house['village_name'] ?? '-') ?></td>
                                        <?php endif; ?>
                                        <td><strong><?= htmlspecialchars($house['house_number']) ?></strong></td>
                                        <td><?= htmlspecialchars($house['complement'] ?? '-') ?></td>
                                        <td>
                                            <?php
                                            $residentModel = new Resident();
                                            $count = $residentModel->countByHouse($house['id']);
                                            echo $count;
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($house['status'] === 'ativo'): ?>
                                                <span class="badge bg-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= SITE_URL ?>/houses/edit.php?id=<?= $house['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($count == 0): ?>
                                            <a href="<?= SITE_URL ?>/houses/delete.php?id=<?= $house['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Tem certeza que deseja excluir esta casa?')"
                                               title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
