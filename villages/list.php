<?php
/**
 * E-Condo Packages - Listar Villages
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

$villageModel = new Village();
$villages = $villageModel->getAll();

$pageTitle = 'Villages';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-buildings"></i> Villages
                </h1>
                <a href="<?= SITE_URL ?>/villages/create.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nova Village
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list"></i> 
                        <?= count($villages) ?> village(s) cadastrada(s)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($villages)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Nenhuma village cadastrada ainda.
                            <a href="<?= SITE_URL ?>/villages/create.php" class="alert-link">Cadastrar primeira village</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Nº de Casas</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($villages as $village): ?>
                                    <tr>
                                        <td><?= $village['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($village['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($village['description'] ?? '-') ?></td>
                                        <td>
                                            <?php
                                            $houseModel = new House();
                                            $count = $houseModel->countByVillage($village['id']);
                                            echo $count;
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($village['status'] === 'ativo'): ?>
                                                <span class="badge bg-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= SITE_URL ?>/villages/edit.php?id=<?= $village['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= SITE_URL ?>/houses/list.php?village_id=<?= $village['id'] ?>" 
                                               class="btn btn-sm btn-outline-info" title="Ver Casas">
                                                <i class="bi bi-house"></i>
                                            </a>
                                            <?php if ($count == 0): ?>
                                            <a href="<?= SITE_URL ?>/villages/delete.php?id=<?= $village['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Tem certeza que deseja excluir esta village?')"
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
