<?php
/**
 * E-Condo Packages - Ver Detalhes da Encomenda
 */

require_once '../config/config.php';

// Verificar autenticação
if (!isLoggedIn()) {
    redirect('/login.php');
}

$packageId = $_GET['id'] ?? '';

if (empty($packageId)) {
    setFlash('danger', 'Encomenda não encontrada.');
    redirect('/packages/list.php');
}

$packageModel = new Package();
$whatsappService = new WhatsAppService();

$package = $packageModel->findById($packageId);

if (!$package) {
    setFlash('danger', 'Encomenda não encontrada.');
    redirect('/packages/list.php');
}

// Buscar informações completas
$packageFull = $packageModel->findByTrackingCode($package['tracking_code']);
$history = $packageModel->getHistory($packageId);
$notifications = $whatsappService->getPackageNotifications($packageId);

$pageTitle = 'Detalhes da Encomenda';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-box-seam"></i> Detalhes da Encomenda
                </h1>
                <a href="<?= SITE_URL ?>/packages/list.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Informações Principais -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações da Encomenda</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Código de Rastreamento:</strong><br>
                            <span class="fs-4 text-primary"><?= htmlspecialchars($packageFull['tracking_code']) ?></span>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>Localização:</strong><br>
                            <?php
                            $locationBadge = [
                                'portaria' => '<span class="badge bg-warning fs-6">Portaria</span>',
                                'administracao' => '<span class="badge bg-info fs-6">Administração</span>',
                                'retirada' => '<span class="badge bg-secondary fs-6">Retirada</span>'
                            ];
                            echo $locationBadge[$packageFull['current_location']] ?? '';
                            ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>Status:</strong><br>
                            <?php
                            $statusBadge = [
                                'pendente' => '<span class="badge bg-warning fs-6">Pendente</span>',
                                'transferida' => '<span class="badge bg-info fs-6">Transferida</span>',
                                'retirada' => '<span class="badge bg-success fs-6">Retirada</span>'
                            ];
                            echo $statusBadge[$packageFull['status']] ?? '';
                            ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="fw-bold mb-3">Dados do Condômino</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-person"></i> Nome:</strong><br>
                            <?= htmlspecialchars($packageFull['resident_name']) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-credit-card"></i> CPF:</strong><br>
                            <?= formatCPF($packageFull['resident_cpf']) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-telephone"></i> Telefone:</strong><br>
                            <?= formatPhone($packageFull['resident_phone']) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-house"></i> Endereço:</strong><br>
                            <?= htmlspecialchars($packageFull['village_name']) ?> - Casa <?= htmlspecialchars($packageFull['house_number']) ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($packageFull['observations'])): ?>
                    <hr>
                    <h6 class="fw-bold mb-2">Observações</h6>
                    <p><?= nl2br(htmlspecialchars($packageFull['observations'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Histórico -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Histórico de Movimentações</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($history)): ?>
                        <p class="text-muted mb-0">Nenhuma movimentação registrada.</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($history as $item): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <strong>
                                        <?php
                                        $actionLabels = [
                                            'recebida' => '<i class="bi bi-box-seam text-primary"></i> Encomenda Recebida',
                                            'transferida' => '<i class="bi bi-arrow-right-circle text-info"></i> Transferida',
                                            'retirada' => '<i class="bi bi-check-circle text-success"></i> Retirada'
                                        ];
                                        echo $actionLabels[$item['action']] ?? $item['action'];
                                        ?>
                                    </strong>
                                    <small class="text-muted"><?= formatDateBR($item['created_at']) ?></small>
                                </div>
                                <?php if ($item['from_location'] || $item['to_location']): ?>
                                    <small class="text-muted">
                                        <?= ucfirst($item['from_location'] ?? '') ?> 
                                        <?= $item['from_location'] && $item['to_location'] ? '→' : '' ?> 
                                        <?= ucfirst($item['to_location'] ?? '') ?>
                                    </small><br>
                                <?php endif; ?>
                                <?php if ($item['user_name']): ?>
                                    <small class="text-muted">Por: <?= htmlspecialchars($item['user_name']) ?></small><br>
                                <?php endif; ?>
                                <?php if ($item['notes']): ?>
                                    <small><?= htmlspecialchars($item['notes']) ?></small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Notificações WhatsApp -->
            <?php if (!empty($notifications)): ?>
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-whatsapp"></i> Notificações WhatsApp</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Data/Hora</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notifications as $notif): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'recebimento' => '<i class="bi bi-box-seam"></i> Recebimento',
                                            'retirada' => '<i class="bi bi-check-circle"></i> Retirada',
                                            'qrcode' => '<i class="bi bi-qr-code"></i> QR Code'
                                        ];
                                        echo $typeLabels[$notif['notification_type']] ?? $notif['notification_type'];
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusBadge = [
                                            'enviada' => '<span class="badge bg-success">Enviada</span>',
                                            'erro' => '<span class="badge bg-danger">Erro</span>',
                                            'pendente' => '<span class="badge bg-warning">Pendente</span>'
                                        ];
                                        echo $statusBadge[$notif['status']] ?? '';
                                        ?>
                                    </td>
                                    <td>
                                        <small><?= formatDateBR($notif['sent_at'] ?? $notif['created_at']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($notif['status'] === 'erro' && !empty($notif['error_message'])): ?>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#errorModal<?= $notif['id'] ?>">
                                                    <i class="bi bi-exclamation-triangle"></i> Ver Erro
                                                </button>
                                                <form method="POST" action="<?= SITE_URL ?>/packages/resend_notification.php" style="display: inline;">
                                                    <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                                                    <input type="hidden" name="package_id" value="<?= $packageId ?>">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm" 
                                                            title="Reenviar notificação">
                                                        <i class="bi bi-arrow-clockwise"></i> Reenviar
                                                    </button>
                                                </form>
                                            </div>
                                            
                                            <!-- Modal de Erro -->
                                            <div class="modal fade" id="errorModal<?= $notif['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">
                                                                <i class="bi bi-exclamation-triangle"></i> Erro no Envio
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>Mensagem de Erro:</strong></p>
                                                            <div class="alert alert-danger">
                                                                <?= nl2br(htmlspecialchars($notif['error_message'])) ?>
                                                            </div>
                                                            
                                                            <p><strong>Telefone:</strong> <?= formatPhone($notif['phone']) ?></p>
                                                            <p><strong>Data:</strong> <?= formatDateBR($notif['created_at']) ?></p>
                                                            
                                                            <?php if (!empty($notif['response_data'])): ?>
                                                            <details>
                                                                <summary><small>Resposta da API (técnico)</small></summary>
                                                                <pre class="small bg-light p-2 mt-2"><?= htmlspecialchars($notif['response_data']) ?></pre>
                                                            </details>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif ($notif['status'] === 'enviada'): ?>
                                            <small class="text-success">
                                                <i class="bi bi-check-circle"></i> Enviada com sucesso
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <!-- Linha do Tempo -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Linha do Tempo</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong><i class="bi bi-box-seam text-primary"></i> Recebida</strong><br>
                        <small><?= formatDateBR($packageFull['received_at']) ?></small><br>
                        <small class="text-muted">Por: <?= htmlspecialchars($packageFull['received_by_name'] ?? '-') ?></small>
                    </div>
                    
                    <?php if ($packageFull['transferred_at']): ?>
                    <div class="mb-3">
                        <strong><i class="bi bi-arrow-right-circle text-info"></i> Transferida</strong><br>
                        <small><?= formatDateBR($packageFull['transferred_at']) ?></small><br>
                        <small class="text-muted">Por: <?= htmlspecialchars($packageFull['transferred_by_name'] ?? '-') ?></small>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($packageFull['picked_up_at']): ?>
                    <div class="mb-3">
                        <strong><i class="bi bi-check-circle text-success"></i> Retirada</strong><br>
                        <small><?= formatDateBR($packageFull['picked_up_at']) ?></small><br>
                        <small class="text-muted">Por: <?= htmlspecialchars($packageFull['picked_up_by_name'] ?? '-') ?></small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Ações -->
            <?php if ($packageFull['status'] !== 'retirada'): ?>
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge"></i> Ações</h5>
                </div>
                <div class="card-body">
                    <?php if ($packageFull['current_location'] === 'portaria' && hasRole(['admin', 'porteiro', 'administracao'])): ?>
                    <form method="POST" action="<?= SITE_URL ?>/packages/transfer.php" class="mb-2">
                        <input type="hidden" name="package_ids[]" value="<?= $packageId ?>">
                        <button type="submit" class="btn btn-warning w-100" 
                                onclick="return confirm('Transferir esta encomenda para administração?')">
                            <i class="bi bi-arrow-right-circle"></i> Transferir para Administração
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <a href="<?= SITE_URL ?>/packages/pickup.php" class="btn btn-success w-100">
                        <i class="bi bi-check-circle"></i> Registrar Retirada
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
