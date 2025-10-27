<?php
/**
 * E-Condo Packages - Listar Condôminos
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

$residentModel = new Resident();
$residents = $residentModel->getAllWithHouseInfo();

$pageTitle = 'Condôminos';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-people"></i> Condôminos
                </h1>
                <a href="<?= SITE_URL ?>/residents/create.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Novo Condômino
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
                        <?= count($residents) ?> condômino(s) cadastrado(s)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($residents)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Nenhum condômino cadastrado ainda.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>CPF</th>
                                        <th>Telefone</th>
                                        <th>Village/Casa</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($residents as $resident): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($resident['full_name']) ?></td>
                                        <td><?= formatCPF($resident['cpf']) ?></td>
                                        <td><?= formatPhone($resident['phone']) ?></td>
                                        <td>
                                            <?php if ($resident['village_name']): ?>
                                                <?= htmlspecialchars($resident['village_name']) ?> - 
                                                Casa <?= htmlspecialchars($resident['house_number']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Não atribuído</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($resident['status'] === 'ativo'): ?>
                                                <span class="badge bg-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= SITE_URL ?>/residents/edit.php?id=<?= $resident['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= SITE_URL ?>/residents/delete.php?id=<?= $resident['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               data-confirm="Tem certeza que deseja excluir este condômino?"
                                               title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </a>
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
