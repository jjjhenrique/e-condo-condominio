<?php
/**
 * E-Condo Packages - Logs do Sistema
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

$logModel = new SystemLog();

// Filtros
$filters = [
    'user_id' => $_GET['user_id'] ?? '',
    'action' => $_GET['action'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
];

// Buscar logs
$logs = $logModel->search($filters);

// Buscar usuários para filtro
$userModel = new User();
$users = $userModel->getAll();

$pageTitle = 'Logs do Sistema';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-file-text"></i> Logs do Sistema
                </h1>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label for="user_id" class="form-label">Usuário</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">Todos</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= $filters['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="action" class="form-label">Ação</label>
                            <select class="form-select" id="action" name="action">
                                <option value="">Todas</option>
                                <option value="login" <?= $filters['action'] === 'login' ? 'selected' : '' ?>>Login</option>
                                <option value="logout" <?= $filters['action'] === 'logout' ? 'selected' : '' ?>>Logout</option>
                                <option value="package_received" <?= $filters['action'] === 'package_received' ? 'selected' : '' ?>>Encomenda Recebida</option>
                                <option value="package_transferred" <?= $filters['action'] === 'package_transferred' ? 'selected' : '' ?>>Encomenda Transferida</option>
                                <option value="package_picked_up" <?= $filters['action'] === 'package_picked_up' ? 'selected' : '' ?>>Encomenda Retirada</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="<?= htmlspecialchars($filters['date_from']) ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Data Final</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="<?= htmlspecialchars($filters['date_to']) ?>">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a href="<?= SITE_URL ?>/admin/logs.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Logs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list"></i> 
                        <?= count($logs) ?> registro(s) encontrado(s)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Nenhum log encontrado.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Data/Hora</th>
                                        <th>Usuário</th>
                                        <th>Ação</th>
                                        <th>Descrição</th>
                                        <th>IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><small><?= formatDateBR($log['created_at']) ?></small></td>
                                        <td><small><?= htmlspecialchars($log['user_name'] ?? 'Sistema') ?></small></td>
                                        <td>
                                            <small>
                                            <?php
                                            $actionBadge = [
                                                'login' => '<span class="badge bg-success">Login</span>',
                                                'logout' => '<span class="badge bg-secondary">Logout</span>',
                                                'package_received' => '<span class="badge bg-primary">Recebida</span>',
                                                'package_transferred' => '<span class="badge bg-info">Transferida</span>',
                                                'package_picked_up' => '<span class="badge bg-success">Retirada</span>',
                                                'user_created' => '<span class="badge bg-primary">Usuário Criado</span>',
                                                'user_updated' => '<span class="badge bg-warning">Usuário Atualizado</span>',
                                                'settings_updated' => '<span class="badge bg-info">Config. Atualizada</span>',
                                            ];
                                            echo $actionBadge[$log['action']] ?? '<span class="badge bg-secondary">' . $log['action'] . '</span>';
                                            ?>
                                            </small>
                                        </td>
                                        <td><small><?= htmlspecialchars($log['description']) ?></small></td>
                                        <td><small><?= htmlspecialchars($log['ip_address'] ?? '-') ?></small></td>
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
