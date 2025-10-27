<?php
/**
 * E-Condo Packages - Estatísticas do Sistema
 */

require_once '../config/config.php';

// Verificar autenticação
if (!isLoggedIn()) {
    redirect('/login.php');
}

$packageModel = new Package();
$residentModel = new Resident();
$villageModel = new Village();
$houseModel = new House();

// Estatísticas gerais
$stats = [
    'total_packages' => $packageModel->count(),
    'packages_today' => $packageModel->getReceivedToday(),
    'packages_pending' => $packageModel->countByStatus('pendente') + $packageModel->countByStatus('transferida'),
    'packages_picked_up' => $packageModel->countByStatus('retirada'),
    'packages_at_portaria' => $packageModel->countByLocation('portaria'),
    'packages_at_admin' => $packageModel->countByLocation('administracao'),
    'total_residents' => $residentModel->count(),
    'active_residents' => count($residentModel->findWhere(['status' => 'ativo'])),
    'total_villages' => $villageModel->count(),
    'total_houses' => $houseModel->count(),
];

// Estatísticas por período
$period = $_GET['period'] ?? '7days';

switch ($period) {
    case 'today':
        $dateFrom = date('Y-m-d 00:00:00');
        $dateTo = date('Y-m-d 23:59:59');
        $periodLabel = 'Hoje';
        break;
    case '7days':
        $dateFrom = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $dateTo = date('Y-m-d 23:59:59');
        $periodLabel = 'Últimos 7 dias';
        break;
    case '30days':
        $dateFrom = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $dateTo = date('Y-m-d 23:59:59');
        $periodLabel = 'Últimos 30 dias';
        break;
    case 'month':
        $dateFrom = date('Y-m-01 00:00:00');
        $dateTo = date('Y-m-t 23:59:59');
        $periodLabel = 'Este mês';
        break;
    default:
        $dateFrom = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $dateTo = date('Y-m-d 23:59:59');
        $periodLabel = 'Últimos 7 dias';
}

// Encomendas por período
$packagesByPeriod = $packageModel->search([
    'date_from' => $dateFrom,
    'date_to' => $dateTo
]);

// Estatísticas por village
$packagesByVillage = $packageModel->query("
    SELECT v.name as village_name, COUNT(p.id) as total
    FROM packages p
    INNER JOIN residents r ON p.resident_id = r.id
    INNER JOIN houses h ON r.house_id = h.id
    INNER JOIN villages v ON h.village_id = v.id
    GROUP BY v.id, v.name
    ORDER BY total DESC
    LIMIT 10
");

// Top condôminos
$topResidents = $packageModel->query("
    SELECT r.full_name, COUNT(p.id) as total
    FROM packages p
    INNER JOIN residents r ON p.resident_id = r.id
    GROUP BY r.id, r.full_name
    ORDER BY total DESC
    LIMIT 10
");

$pageTitle = 'Estatísticas';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-graph-up"></i> Estatísticas do Sistema
                </h1>
                <div>
                    <select class="form-select" onchange="window.location.href='?period='+this.value">
                        <option value="today" <?= $period === 'today' ? 'selected' : '' ?>>Hoje</option>
                        <option value="7days" <?= $period === '7days' ? 'selected' : '' ?>>Últimos 7 dias</option>
                        <option value="30days" <?= $period === '30days' ? 'selected' : '' ?>>Últimos 30 dias</option>
                        <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Este mês</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas Gerais -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total de Encomendas</h6>
                            <h2 class="mb-0"><?= $stats['total_packages'] ?></h2>
                        </div>
                        <div class="text-primary" style="font-size: 3rem;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Recebidas Hoje</h6>
                            <h2 class="mb-0"><?= $stats['packages_today'] ?></h2>
                        </div>
                        <div class="text-success" style="font-size: 3rem;">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pendentes</h6>
                            <h2 class="mb-0"><?= $stats['packages_pending'] ?></h2>
                        </div>
                        <div class="text-warning" style="font-size: 3rem;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Retiradas</h6>
                            <h2 class="mb-0"><?= $stats['packages_picked_up'] ?></h2>
                        </div>
                        <div class="text-info" style="font-size: 3rem;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Localização das Encomendas -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Localização Atual</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-warning"><?= $stats['packages_at_portaria'] ?></h3>
                            <p class="text-muted mb-0">Na Portaria</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-info"><?= $stats['packages_at_admin'] ?></h3>
                            <p class="text-muted mb-0">Na Administração</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Cadastros</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="text-primary"><?= $stats['total_villages'] ?></h3>
                            <p class="text-muted mb-0">Villages</p>
                        </div>
                        <div class="col-4">
                            <h3 class="text-success"><?= $stats['total_houses'] ?></h3>
                            <p class="text-muted mb-0">Casas</p>
                        </div>
                        <div class="col-4">
                            <h3 class="text-info"><?= $stats['active_residents'] ?></h3>
                            <p class="text-muted mb-0">Condôminos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas do Período -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-buildings"></i> Encomendas por Village (<?= $periodLabel ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($packagesByVillage)): ?>
                        <p class="text-muted">Nenhuma encomenda no período.</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Village</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($packagesByVillage as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['village_name']) ?></td>
                                    <td class="text-end"><strong><?= $item['total'] ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-person-check"></i> Top 10 Condôminos</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($topResidents)): ?>
                        <p class="text-muted">Nenhuma encomenda registrada.</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Condômino</th>
                                    <th class="text-end">Encomendas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topResidents as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['full_name']) ?></td>
                                    <td class="text-end"><strong><?= $item['total'] ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Encomendas do Período -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-range"></i> 
                        Encomendas - <?= $periodLabel ?> (<?= count($packagesByPeriod) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <strong>Período:</strong> <?= date('d/m/Y', strtotime($dateFrom)) ?> até <?= date('d/m/Y', strtotime($dateTo)) ?>
                    </p>
                    
                    <?php if (!empty($packagesByPeriod)): ?>
                        <a href="<?= SITE_URL ?>/reports/export_packages.php?date_from=<?= date('Y-m-d', strtotime($dateFrom)) ?>&date_to=<?= date('Y-m-d', strtotime($dateTo)) ?>" 
                           class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                        </a>
                        <a href="<?= SITE_URL ?>/reports/packages.php?generate=1&date_from=<?= date('Y-m-d', strtotime($dateFrom)) ?>&date_to=<?= date('Y-m-d', strtotime($dateTo)) ?>" 
                           class="btn btn-primary">
                            <i class="bi bi-file-earmark-text"></i> Ver Relatório Completo
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
