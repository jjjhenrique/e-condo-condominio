<?php
/**
 * E-Condo Packages - Dashboard Principal
 */

require_once 'config/config.php';

// Verificar autenticação
if (!isLoggedIn()) {
    redirect('/login.php');
}

// Carregar models necessários
$packageModel = new Package();
$user = getCurrentUser();

// Obter estatísticas do dashboard
$stats = [
    'received_today' => $packageModel->getReceivedToday(),
    'pending_portaria' => $packageModel->countByLocation('portaria'),
    'pending_administracao' => $packageModel->countByLocation('administracao'),
    'picked_up_today' => $packageModel->getPickedUpToday(),
    'total_pending' => $packageModel->countByStatus('pendente'),
    'total_transferred' => $packageModel->countByStatus('transferida'),
];

// Buscar encomendas recentes
$recentPackages = $packageModel->query(
    "SELECT p.*, 
    r.full_name as resident_name, 
    h.house_number, 
    v.name as village_name
    FROM packages p
    INNER JOIN residents r ON p.resident_id = r.id
    INNER JOIN houses h ON p.house_id = h.id
    INNER JOIN villages v ON h.village_id = v.id
    ORDER BY p.received_at DESC
    LIMIT 10"
);

$pageTitle = 'Dashboard';
require_once 'app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="bi bi-speedometer2"></i> Dashboard
            </h1>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Recebidas Hoje</h6>
                            <h2 class="mb-0"><?= $stats['received_today'] ?></h2>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-box-seam fs-1"></i>
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
                            <h6 class="text-muted mb-2">Na Portaria</h6>
                            <h2 class="mb-0"><?= $stats['pending_portaria'] ?></h2>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-door-open fs-1"></i>
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
                            <h6 class="text-muted mb-2">Na Administração</h6>
                            <h2 class="mb-0"><?= $stats['pending_administracao'] ?></h2>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-building fs-1"></i>
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
                            <h6 class="text-muted mb-2">Retiradas Hoje</h6>
                            <h2 class="mb-0"><?= $stats['picked_up_today'] ?></h2>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge"></i> Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if (hasRole(['admin', 'porteiro'])): ?>
                        <div class="col-md-3">
                            <a href="packages/receive.php" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-plus-circle"></i><br>
                                Receber Encomenda
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-3">
                            <a href="packages/pickup.php" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-check2-square"></i><br>
                                Retirar Encomenda
                            </a>
                        </div>
                        
                        <?php if (hasRole(['admin', 'porteiro', 'administracao'])): ?>
                        <div class="col-md-3">
                            <a href="packages/transfer.php" class="btn btn-warning btn-lg w-100">
                                <i class="bi bi-arrow-left-right"></i><br>
                                Transferir Encomendas
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-3">
                            <a href="packages/list.php" class="btn btn-info btn-lg w-100">
                                <i class="bi bi-list-ul"></i><br>
                                Ver Todas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Encomendas Recentes -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Encomendas Recentes</h5>
                    <a href="packages/list.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentPackages)): ?>
                        <p class="text-muted text-center py-4">Nenhuma encomenda registrada ainda.</p>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPackages as $package): ?>
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

<?php require_once 'app/views/layouts/footer.php'; ?>
