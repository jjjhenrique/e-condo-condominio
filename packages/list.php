<?php
/**
 * E-Condo Packages - Listar Encomendas
 */

require_once '../config/config.php';

// Verificar autenticação
if (!isLoggedIn()) {
    redirect('/login.php');
}

$packageModel = new Package();

// Filtros
$filters = [
    'status' => $_GET['status'] ?? '',
    'location' => $_GET['location'] ?? '',
    'search' => $_GET['search'] ?? '',
];

// Buscar encomendas
if (!empty($filters['search'])) {
    // Busca rápida por código de rastreamento ou nome do condômino
    $packages = $packageModel->quickSearch($filters['search']);
} elseif (!empty($filters['status']) || !empty($filters['location'])) {
    $searchFilters = [];
    if (!empty($filters['status'])) {
        $searchFilters['status'] = $filters['status'];
    }
    if (!empty($filters['location'])) {
        $searchFilters['current_location'] = $filters['location'];
    }
    $packages = $packageModel->search($searchFilters);
} else {
    $packages = $packageModel->getAllWithFullInfo();
}

$pageTitle = 'Listar Encomendas';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-list-ul"></i> Encomendas
                </h1>
                <div>
                    <?php if (hasRole(['admin', 'porteiro'])): ?>
                    <a href="<?= SITE_URL ?>/packages/receive.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Receber Encomenda
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">
                                <i class="bi bi-search"></i> Busca Rápida
                            </label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($filters['search']) ?>" 
                                   placeholder="Digite o código ou nome do condômino"
                                   style="text-transform: uppercase;">
                            <small class="text-muted">Ex: PKG123456 ou João Silva</small>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="pendente" <?= $filters['status'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="transferida" <?= $filters['status'] === 'transferida' ? 'selected' : '' ?>>Transferida</option>
                                <option value="retirada" <?= $filters['status'] === 'retirada' ? 'selected' : '' ?>>Retirada</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="location" class="form-label">Localização</label>
                            <select class="form-select" id="location" name="location">
                                <option value="">Todas</option>
                                <option value="portaria" <?= $filters['location'] === 'portaria' ? 'selected' : '' ?>>Portaria</option>
                                <option value="administracao" <?= $filters['location'] === 'administracao' ? 'selected' : '' ?>>Administração</option>
                                <option value="retirada" <?= $filters['location'] === 'retirada' ? 'selected' : '' ?>>Retirada</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <a href="<?= SITE_URL ?>/packages/list.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Encomendas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam"></i> 
                        <?= count($packages) ?> encomenda(s) encontrada(s)
                    </h5>
                    <?php if (!empty($packages)): ?>
                    <a href="<?= SITE_URL ?>/reports/export_packages.php?<?= http_build_query($filters) ?>" 
                       class="btn btn-sm btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($packages)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Nenhuma encomenda encontrada.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Condômino</th>
                                        <th>Village/Casa</th>
                                        <th>Localização</th>
                                        <th>Status</th>
                                        <th>Recebida em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($packages as $package): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($package['tracking_code']) ?></strong></td>
                                        <td><?= htmlspecialchars($package['resident_name']) ?></td>
                                        <td><?= htmlspecialchars($package['village_name']) ?> - <?= htmlspecialchars($package['house_number']) ?></td>
                                        <td>
                                            <?php
                                            $locationBadge = [
                                                'portaria' => '<span class="badge bg-warning">Portaria</span>',
                                                'administracao' => '<span class="badge bg-info">Administração</span>',
                                                'retirada' => '<span class="badge bg-secondary">Retirada</span>'
                                            ];
                                            echo $locationBadge[$package['current_location']] ?? '';
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusBadge = [
                                                'pendente' => '<span class="badge bg-warning">Pendente</span>',
                                                'transferida' => '<span class="badge bg-info">Transferida</span>',
                                                'retirada' => '<span class="badge bg-success">Retirada</span>'
                                            ];
                                            echo $statusBadge[$package['status']] ?? '';
                                            ?>
                                        </td>
                                        <td><?= formatDateBR($package['received_at']) ?></td>
                                        <td>
                                            <a href="<?= SITE_URL ?>/packages/view.php?id=<?= $package['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Ver Detalhes">
                                                <i class="bi bi-eye"></i>
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

<?php
$extraScripts = <<<'SCRIPT'
<script>
// Auto-uppercase no campo de busca
document.getElementById('search').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

// Atalho: Ctrl/Cmd + K para focar no campo de busca
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('search').focus();
    }
});
</script>
SCRIPT;

require_once '../app/views/layouts/footer.php';
?>
