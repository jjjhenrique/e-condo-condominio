<?php
/**
 * E-Condo Packages - Relatório de Encomendas
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
$stats = [];

// Processar busca
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['generate'])) {
    $searched = true;
    
    $filters = [
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
    } else {
        $packages = $packageModel->getAllWithFullInfo();
    }
    
    // Calcular estatísticas
    $stats = [
        'total' => count($packages),
        'portaria' => 0,
        'administracao' => 0,
        'retirada' => 0,
        'pendente' => 0,
        'transferida' => 0,
    ];
    
    foreach ($packages as $pkg) {
        $stats[$pkg['current_location']]++;
        $stats[$pkg['status']]++;
    }
}

$villages = $villageModel->getActive();

$pageTitle = 'Relatório de Encomendas';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-file-earmark-bar-graph"></i> Relatório de Encomendas
                </h1>
                <a href="<?= SITE_URL ?>/index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="generate" value="1">
                        
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
                            <label for="current_location" class="form-label">Localização</label>
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
                                <i class="bi bi-graph-up"></i> Gerar Relatório
                            </button>
                            <a href="<?= SITE_URL ?>/reports/packages.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <?php if ($searched): ?>
                <!-- Estatísticas -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="text-muted">Total de Encomendas</h6>
                                <h2 class="mb-0"><?= $stats['total'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="text-muted">Na Portaria</h6>
                                <h2 class="mb-0"><?= $stats['portaria'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-muted">Retiradas</h6>
                                <h2 class="mb-0"><?= $stats['retirada'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabela de Resultados -->
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-table"></i> Resultados
                        </h5>
                        <?php if (!empty($packages)): ?>
                        <a href="<?= SITE_URL ?>/reports/export_packages.php?<?= http_build_query($_GET) ?>" 
                           class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($packages)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Nenhuma encomenda encontrada com os filtros selecionados.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Condômino</th>
                                            <th>Village/Casa</th>
                                            <th>Localização</th>
                                            <th>Status</th>
                                            <th>Recebida</th>
                                            <th>Retirada</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($packages as $package): ?>
                                        <tr>
                                            <td><small><strong><?= htmlspecialchars($package['tracking_code']) ?></strong></small></td>
                                            <td><small><?= htmlspecialchars($package['resident_name']) ?></small></td>
                                            <td><small><?= htmlspecialchars($package['village_name']) ?> - <?= htmlspecialchars($package['house_number']) ?></small></td>
                                            <td>
                                                <small>
                                                <?php
                                                $locationBadge = [
                                                    'portaria' => '<span class="badge bg-warning">Portaria</span>',
                                                    'administracao' => '<span class="badge bg-info">Administração</span>',
                                                    'retirada' => '<span class="badge bg-secondary">Retirada</span>'
                                                ];
                                                echo $locationBadge[$package['current_location']] ?? '';
                                                ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                <?php
                                                $statusBadge = [
                                                    'pendente' => '<span class="badge bg-warning">Pendente</span>',
                                                    'transferida' => '<span class="badge bg-info">Transferida</span>',
                                                    'retirada' => '<span class="badge bg-success">Retirada</span>'
                                                ];
                                                echo $statusBadge[$package['status']] ?? '';
                                                ?>
                                                </small>
                                            </td>
                                            <td><small><?= formatDateBR($package['received_at']) ?></small></td>
                                            <td><small><?= $package['picked_up_at'] ? formatDateBR($package['picked_up_at']) : '-' ?></small></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-file-earmark-bar-graph text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Gerar Relatório de Encomendas</h4>
                        <p class="text-muted">
                            Selecione os filtros desejados no painel ao lado e clique em "Gerar Relatório"
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
