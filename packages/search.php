<?php
/**
 * E-Condo Packages - Busca Avançada de Encomendas
 */

require_once '../config/config.php';

// Verificar autenticação
if (!isLoggedIn()) {
    redirect('/login.php');
}

$packageModel = new Package();
$villageModel = new Village();

$packages = [];
$searched = false;

// Processar busca
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['search'])) {
    $searched = true;
    
    $filters = [
        'tracking_code' => sanitize($_GET['tracking_code'] ?? ''),
        'resident_name' => sanitize($_GET['resident_name'] ?? ''),
        'village_id' => $_GET['village_id'] ?? '',
        'current_location' => $_GET['current_location'] ?? '',
        'status' => $_GET['status'] ?? '',
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
    ];
    
    // Remover filtros vazios
    $filters = array_filter($filters, function($value) {
        return $value !== '';
    });
    
    if (!empty($filters)) {
        $packages = $packageModel->search($filters);
    }
}

$villages = $villageModel->getActive();

$pageTitle = 'Busca Avançada';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-search"></i> Busca Avançada de Encomendas
                </h1>
                <a href="<?= SITE_URL ?>/packages/list.php" class="btn btn-outline-secondary">
                    <i class="bi bi-list"></i> Ver Todas
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros de Busca</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="search" value="1">
                        
                        <div class="mb-3">
                            <label for="tracking_code" class="form-label">Código de Rastreamento</label>
                            <input type="text" class="form-control" id="tracking_code" name="tracking_code" 
                                   placeholder="Ex: PKG123456789" value="<?= htmlspecialchars($_GET['tracking_code'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="resident_name" class="form-label">Nome do Condômino</label>
                            <input type="text" class="form-control" id="resident_name" name="resident_name" 
                                   placeholder="Digite o nome" value="<?= htmlspecialchars($_GET['resident_name'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="village_id" class="form-label">Village</label>
                            <select class="form-select" id="village_id" name="village_id">
                                <option value="">Todas</option>
                                <?php foreach ($villages as $village): ?>
                                    <option value="<?= $village['id'] ?>" 
                                            <?= ($_GET['village_id'] ?? '') == $village['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($village['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="current_location" class="form-label">Localização Atual</label>
                            <select class="form-select" id="current_location" name="current_location">
                                <option value="">Todas</option>
                                <option value="portaria" <?= ($_GET['current_location'] ?? '') === 'portaria' ? 'selected' : '' ?>>Portaria</option>
                                <option value="administracao" <?= ($_GET['current_location'] ?? '') === 'administracao' ? 'selected' : '' ?>>Administração</option>
                                <option value="retirada" <?= ($_GET['current_location'] ?? '') === 'retirada' ? 'selected' : '' ?>>Retirada</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="pendente" <?= ($_GET['status'] ?? '') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="transferida" <?= ($_GET['status'] ?? '') === 'transferida' ? 'selected' : '' ?>>Transferida</option>
                                <option value="retirada" <?= ($_GET['status'] ?? '') === 'retirada' ? 'selected' : '' ?>>Retirada</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_from" class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_to" class="form-label">Data Final</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <a href="<?= SITE_URL ?>/packages/search.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpar Filtros
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam"></i> 
                        Resultados da Busca
                        <?php if ($searched): ?>
                            <span class="badge bg-primary"><?= count($packages) ?></span>
                        <?php endif; ?>
                    </h5>
                    <?php if ($searched && !empty($packages)): ?>
                    <a href="<?= SITE_URL ?>/reports/export_packages.php?<?= http_build_query($_GET) ?>" 
                       class="btn btn-sm btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Exportar
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!$searched): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Use os filtros ao lado para buscar encomendas.
                        </div>
                    <?php elseif (empty($packages)): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Nenhuma encomenda encontrada com os filtros selecionados.
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
                                        <th>Recebida</th>
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

<?php require_once '../app/views/layouts/footer.php'; ?>
