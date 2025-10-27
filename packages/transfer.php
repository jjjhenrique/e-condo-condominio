<?php
/**
 * E-Condo Packages - Transferir Encomendas
 */

require_once '../config/config.php';

// Verificar autenticação e permissão
if (!isLoggedIn()) {
    redirect('/login.php');
}

if (!hasRole(['admin', 'porteiro', 'administracao'])) {
    setFlash('danger', 'Você não tem permissão para acessar esta página.');
    redirect('/index.php');
}

$packageModel = new Package();

$errors = [];
$success = false;

// Processar transferência
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $packageIds = $_POST['package_ids'] ?? [];
    
    if (empty($packageIds)) {
        $errors[] = 'Selecione pelo menos uma encomenda para transferir.';
    } else {
        $transferred = 0;
        
        foreach ($packageIds as $packageId) {
            $result = $packageModel->transferToAdministracao($packageId, $_SESSION['user_id']);
            if ($result) {
                $transferred++;
                
                // Registrar log
                $package = $packageModel->findById($packageId);
                $logModel = new SystemLog();
                $logModel->insert([
                    'user_id' => $_SESSION['user_id'],
                    'action' => 'package_transferred',
                    'entity_type' => 'package',
                    'entity_id' => $packageId,
                    'description' => "Encomenda {$package['tracking_code']} transferida para administração",
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
            }
        }
        
        if ($transferred > 0) {
            $success = true;
            setFlash('success', "{$transferred} encomenda(s) transferida(s) com sucesso!");
        } else {
            $errors[] = 'Erro ao transferir encomendas.';
        }
    }
}

// Buscar encomendas pendentes na portaria
$pendingPackages = $packageModel->getPendingAtPortaria();

$pageTitle = 'Transferir Encomendas';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-arrow-left-right"></i> Transferir Encomendas para Administração
                </h1>
                <a href="<?= SITE_URL ?>/packages/list.php" class="btn btn-outline-secondary">
                    <i class="bi bi-list"></i> Ver Todas
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Encomendas Pendentes na Portaria</h5>
                    <span class="badge bg-dark"><?= count($pendingPackages) ?> encomenda(s)</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Erro:</strong>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> Encomendas transferidas com sucesso!
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($pendingPackages)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Não há encomendas pendentes na portaria para transferir.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" id="transferForm">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <th>Código</th>
                                            <th>Condômino</th>
                                            <th>Village/Casa</th>
                                            <th>Telefone</th>
                                            <th>Recebida em</th>
                                            <th>Recebida por</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingPackages as $package): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="package_ids[]" 
                                                       value="<?= $package['id'] ?>" 
                                                       class="form-check-input package-checkbox">
                                            </td>
                                            <td><strong><?= htmlspecialchars($package['tracking_code']) ?></strong></td>
                                            <td><?= htmlspecialchars($package['resident_name']) ?></td>
                                            <td><?= htmlspecialchars($package['village_name']) ?> - <?= htmlspecialchars($package['house_number']) ?></td>
                                            <td><?= formatPhone($package['resident_phone']) ?></td>
                                            <td><?= formatDateBR($package['received_at']) ?></td>
                                            <td><?= htmlspecialchars($package['received_by_name'] ?? '-') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <span id="selectedCount">0</span> encomenda(s) selecionada(s)
                                </div>
                                <button type="submit" class="btn btn-warning btn-lg" id="transferBtn" disabled>
                                    <i class="bi bi-arrow-right-circle"></i> Transferir para Administração
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Sobre a Transferência:</h6>
                    <p class="small">
                        As encomendas são transferidas da portaria para a administração 
                        duas vezes ao dia, ou conforme necessário.
                    </p>
                    
                    <hr>
                    
                    <h6 class="fw-bold">Como funciona:</h6>
                    <ol class="small">
                        <li>Selecione as encomendas a transferir</li>
                        <li>Clique em "Transferir"</li>
                        <li>As encomendas mudarão de localização</li>
                        <li>O histórico será registrado</li>
                    </ol>
                </div>
            </div>
            
            <div class="card border-primary mt-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Estatísticas</h5>
                </div>
                <div class="card-body">
                    <?php
                    $stats = [
                        'portaria' => $packageModel->countByLocation('portaria'),
                        'administracao' => $packageModel->countByLocation('administracao'),
                        'transferred_today' => $packageModel->query(
                            "SELECT COUNT(*) as total FROM packages 
                             WHERE DATE(transferred_at) = CURDATE()"
                        )[0]['total'] ?? 0
                    ];
                    ?>
                    <div class="mb-3">
                        <small class="text-muted">Na Portaria</small>
                        <h3 class="mb-0 text-warning"><?= $stats['portaria'] ?></h3>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Na Administração</small>
                        <h3 class="mb-0 text-info"><?= $stats['administracao'] ?></h3>
                    </div>
                    <div>
                        <small class="text-muted">Transferidas Hoje</small>
                        <h3 class="mb-0 text-primary"><?= $stats['transferred_today'] ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'SCRIPT'
<script>
// Select all checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.package-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

// Update selected count
function updateSelectedCount() {
    const checked = document.querySelectorAll('.package-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked;
    document.getElementById('transferBtn').disabled = checked === 0;
}

// Add event listeners to all checkboxes
document.querySelectorAll('.package-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', updateSelectedCount);
});

// Confirm transfer
document.getElementById('transferForm')?.addEventListener('submit', function(e) {
    const count = document.querySelectorAll('.package-checkbox:checked').length;
    if (!confirm(`Confirma a transferência de ${count} encomenda(s) para a administração?`)) {
        e.preventDefault();
    }
});
</script>
SCRIPT;

require_once '../app/views/layouts/footer.php';
?>
