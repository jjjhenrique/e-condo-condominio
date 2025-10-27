<?php
/**
 * E-Condo Packages - Retirar Encomenda
 */

require_once '../config/config.php';

// Verificar autenticação
if (!isLoggedIn()) {
    redirect('/login.php');
}

$packageModel = new Package();
$whatsappService = new WhatsAppService();

$errors = [];
$success = false;
$package = null;

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trackingCode = strtoupper(sanitize($_POST['tracking_code'] ?? ''));
    
    if (empty($trackingCode)) {
        $errors[] = 'Digite o código de rastreamento.';
    } else {
        // Buscar encomenda
        $package = $packageModel->findByTrackingCode($trackingCode);
        
        if (!$package) {
            $errors[] = 'Encomenda não encontrada com este código.';
        } elseif ($package['status'] === 'retirada') {
            $errors[] = 'Esta encomenda já foi retirada em ' . formatDateBR($package['picked_up_at']) . '.';
        } else {
            // Marcar como retirada
            $result = $packageModel->markAsPickedUp($package['id'], $_SESSION['user_id']);
            
            if ($result) {
                // Registrar log
                $logModel = new SystemLog();
                $logModel->insert([
                    'user_id' => $_SESSION['user_id'],
                    'action' => 'package_picked_up',
                    'entity_type' => 'package',
                    'entity_id' => $package['id'],
                    'description' => "Encomenda {$trackingCode} retirada por {$package['resident_name']}",
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                // Enviar notificação WhatsApp
                if ($whatsappService->isEnabled()) {
                    $whatsappService->sendPackagePickedUpNotification(
                        $package['id'],
                        $package['resident_name'],
                        $package['resident_phone'],
                        $trackingCode
                    );
                }
                
                $success = true;
                setFlash('success', "Encomenda {$trackingCode} retirada com sucesso!");
                
                // Limpar para nova busca
                $package = null;
            } else {
                $errors[] = 'Erro ao registrar retirada da encomenda.';
            }
        }
    }
}

$pageTitle = 'Retirar Encomenda';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-check2-square"></i> Retirar Encomenda
                </h1>
                <a href="<?= SITE_URL ?>/packages/list.php" class="btn btn-outline-secondary">
                    <i class="bi bi-list"></i> Ver Encomendas
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-upc-scan"></i> Buscar Encomenda</h5>
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
                            <i class="bi bi-check-circle"></i> <strong>Encomenda retirada com sucesso!</strong>
                            <?php if ($whatsappService->isEnabled()): ?>
                                <p class="mb-0 mt-2">
                                    <i class="bi bi-whatsapp text-success"></i> Notificação de confirmação enviada via WhatsApp.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="pickupForm">
                        <div class="mb-3">
                            <label for="tracking_code" class="form-label">
                                <i class="bi bi-upc"></i> Código de Rastreamento *
                            </label>
                            <input type="text" class="form-control form-control-lg" id="tracking_code" 
                                   name="tracking_code" placeholder="Ex: PKG123456789" 
                                   required autofocus style="text-transform: uppercase;">
                            <small class="text-muted">Digite o código fornecido ao condômino</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Retirar Encomenda
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if ($package && !$success): ?>
            <div class="card mt-3 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Detalhes da Encomenda</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Código:</strong><br>
                            <span class="fs-4 text-primary"><?= htmlspecialchars($package['tracking_code']) ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong><br>
                            <?php
                            $statusBadge = [
                                'pendente' => '<span class="badge bg-warning fs-6">Pendente</span>',
                                'transferida' => '<span class="badge bg-info fs-6">Transferida</span>',
                                'retirada' => '<span class="badge bg-success fs-6">Retirada</span>'
                            ];
                            echo $statusBadge[$package['status']] ?? '';
                            ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-person"></i> Condômino:</strong><br>
                            <?= htmlspecialchars($package['resident_name']) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-credit-card"></i> CPF:</strong><br>
                            <?= formatCPF($package['resident_cpf']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-telephone"></i> Telefone:</strong><br>
                            <?= formatPhone($package['resident_phone']) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-house"></i> Endereço:</strong><br>
                            <?= htmlspecialchars($package['village_name']) ?> - Casa <?= htmlspecialchars($package['house_number']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-geo-alt"></i> Localização Atual:</strong><br>
                            <?php
                            $locationBadge = [
                                'portaria' => '<span class="badge bg-warning">Portaria</span>',
                                'administracao' => '<span class="badge bg-info">Administração</span>'
                            ];
                            echo $locationBadge[$package['current_location']] ?? '';
                            ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-clock"></i> Recebida em:</strong><br>
                            <?= formatDateBR($package['received_at']) ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($package['observations'])): ?>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <strong><i class="bi bi-chat-left-text"></i> Observações:</strong><br>
                            <?= nl2br(htmlspecialchars($package['observations'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <form method="POST" action="" onsubmit="return confirm('Confirma a retirada desta encomenda?');">
                        <input type="hidden" name="tracking_code" value="<?= htmlspecialchars($package['tracking_code']) ?>">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Confirmar Retirada
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Instruções</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Como retirar uma encomenda:</h6>
                    <ol class="small">
                        <li>Solicite o código de rastreamento ao condômino</li>
                        <li>Digite o código no campo acima</li>
                        <li>Clique em "Retirar Encomenda"</li>
                        <li>Verifique os dados do condômino</li>
                        <li>Confirme a retirada</li>
                        <li>Uma notificação será enviada automaticamente</li>
                    </ol>
                    
                    <hr>
                    
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Importante:</strong> Sempre verifique a identidade do condômino antes de liberar a encomenda.
                    </div>
                </div>
            </div>
            
            <div class="card border-success mt-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Estatísticas de Hoje</h5>
                </div>
                <div class="card-body">
                    <?php
                    $todayStats = [
                        'received' => $packageModel->getReceivedToday(),
                        'picked_up' => $packageModel->getPickedUpToday(),
                        'pending' => $packageModel->countByLocation('portaria') + $packageModel->countByLocation('administracao')
                    ];
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Recebidas:</span>
                        <strong class="text-primary"><?= $todayStats['received'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Retiradas:</span>
                        <strong class="text-success"><?= $todayStats['picked_up'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Pendentes:</span>
                        <strong class="text-warning"><?= $todayStats['pending'] ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'SCRIPT'
<script>
// Auto-uppercase no campo de código
document.getElementById('tracking_code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

// Focus no campo após submit
document.getElementById('pickupForm').addEventListener('submit', function() {
    setTimeout(function() {
        document.getElementById('tracking_code').focus();
    }, 100);
});
</script>
SCRIPT;

require_once '../app/views/layouts/footer.php';
?>
