<?php
/**
 * E-Condo Packages - Receber Encomenda
 */

require_once '../config/config.php';

// Verificar autenticação e permissão
if (!isLoggedIn()) {
    redirect('/login.php');
}

if (!hasRole(['admin', 'porteiro'])) {
    setFlash('danger', 'Você não tem permissão para acessar esta página.');
    redirect('/index.php');
}

$packageModel = new Package();
$residentModel = new Resident();
$villageModel = new Village();
$houseModel = new House();
$whatsappService = new WhatsAppService();

$errors = [];
$success = false;

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $residentId = $_POST['resident_id'] ?? '';
    $houseId = $_POST['house_id'] ?? '';
    $observations = sanitize($_POST['observations'] ?? '');
    
    // Validação
    if (empty($residentId)) {
        $errors[] = 'Selecione um condômino.';
    }
    
    if (empty($houseId)) {
        $errors[] = 'Selecione uma casa.';
    }
    
    if (empty($errors)) {
        try {
            // Gerar código de rastreamento
            $trackingCode = $packageModel->generateTrackingCode();
            
            // Buscar informações do condômino
            $resident = $residentModel->getByIdWithHouseInfo($residentId);
            
            if (!$resident) {
                $errors[] = 'Condômino não encontrado.';
            } else {
                // Inserir encomenda
                $packageData = [
                    'resident_id' => $residentId,
                    'house_id' => $houseId,
                    'tracking_code' => $trackingCode,
                    'current_location' => 'portaria',
                    'status' => 'pendente',
                    'observations' => $observations,
                    'received_by' => $_SESSION['user_id']
                ];
                
                $packageId = $packageModel->insert($packageData);
                
                if ($packageId) {
                    // Registrar log
                    $logModel = new SystemLog();
                    $logModel->insert([
                        'user_id' => $_SESSION['user_id'],
                        'action' => 'package_received',
                        'entity_type' => 'package',
                        'entity_id' => $packageId,
                        'description' => "Encomenda {$trackingCode} recebida para {$resident['full_name']}",
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                    ]);
                    
                    // Enviar notificação WhatsApp
                    if ($whatsappService->isEnabled()) {
                        $whatsappService->sendPackageReceivedNotification(
                            $packageId,
                            $resident['full_name'],
                            $resident['phone'],
                            $trackingCode
                        );
                    }
                    
                    $success = true;
                    setFlash('success', "Encomenda registrada com sucesso! Código: {$trackingCode}");
                    
                    // Redirecionar após 2 segundos
                    header("Refresh: 2; url=" . SITE_URL . "/packages/receive.php");
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Erro ao registrar encomenda: ' . $e->getMessage();
        }
    }
}

// Buscar dados para os dropdowns
$villages = $villageModel->getActive();
$residents = $residentModel->getActiveWithHouseInfo();

$pageTitle = 'Receber Encomenda';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-plus-circle"></i> Receber Encomenda
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
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Dados da Encomenda</h5>
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
                        <div class="alert alert-success alert-permanent">
                            <i class="bi bi-check-circle"></i> <strong>Encomenda registrada com sucesso!</strong>
                            <p class="mb-0 mt-2">
                                Código de rastreamento: <strong class="fs-5"><?= $trackingCode ?></strong>
                            </p>
                            <?php if ($whatsappService->isEnabled()): ?>
                                <p class="mb-0 mt-2">
                                    <i class="bi bi-whatsapp text-success"></i> Notificação enviada via WhatsApp para o condômino.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="receiveForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="village_id" class="form-label">
                                    <i class="bi bi-buildings"></i> Village *
                                </label>
                                <select class="form-select" id="village_id" name="village_id" required>
                                    <option value="">Selecione uma village</option>
                                    <?php foreach ($villages as $village): ?>
                                        <option value="<?= $village['id'] ?>"><?= htmlspecialchars($village['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="house_id" class="form-label">
                                    <i class="bi bi-house"></i> Casa *
                                </label>
                                <select class="form-select" id="house_id" name="house_id" required>
                                    <option value="">Selecione uma village primeiro</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="resident_id" class="form-label">
                                <i class="bi bi-person"></i> Condômino *
                            </label>
                            <select class="form-select" id="resident_id" name="resident_id" required>
                                <option value="">Selecione uma casa primeiro</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observations" class="form-label">
                                <i class="bi bi-chat-left-text"></i> Observações
                            </label>
                            <textarea class="form-control" id="observations" name="observations" 
                                      rows="3" placeholder="Informações adicionais sobre a encomenda (opcional)"></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Registrar Encomenda
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Como funciona:</h6>
                    <ol class="small">
                        <li>Selecione a village e a casa do condômino</li>
                        <li>Selecione o condômino destinatário</li>
                        <li>Adicione observações se necessário</li>
                        <li>Clique em "Registrar Encomenda"</li>
                        <li>Um código único será gerado automaticamente</li>
                        <li>O condômino receberá uma notificação via WhatsApp</li>
                    </ol>
                </div>
            </div>
            
            <div class="card border-warning mt-3">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Últimas Encomendas</h5>
                </div>
                <div class="card-body">
                    <?php
                    $recentPackages = $packageModel->query(
                        "SELECT p.tracking_code, r.full_name, p.received_at
                         FROM packages p
                         INNER JOIN residents r ON p.resident_id = r.id
                         ORDER BY p.received_at DESC
                         LIMIT 5"
                    );
                    
                    if (empty($recentPackages)):
                    ?>
                        <p class="text-muted small mb-0">Nenhuma encomenda registrada ainda.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentPackages as $pkg): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <strong class="small"><?= htmlspecialchars($pkg['tracking_code']) ?></strong>
                                        <small class="text-muted"><?= date('H:i', strtotime($pkg['received_at'])) ?></small>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($pkg['full_name']) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$siteUrl = SITE_URL;
$extraScripts = <<<SCRIPT
<script>
// Dados de casas e condôminos por village
const housesByVillage = {};
const residentsByHouse = {};

// Carregar dados via AJAX
document.addEventListener('DOMContentLoaded', function() {
    // Quando selecionar village, carregar casas
    document.getElementById('village_id').addEventListener('change', function() {
        const villageId = this.value;
        const houseSelect = document.getElementById('house_id');
        const residentSelect = document.getElementById('resident_id');
        
        houseSelect.innerHTML = '<option value="">Carregando...</option>';
        residentSelect.innerHTML = '<option value="">Selecione uma casa primeiro</option>';
        
        if (villageId) {
            fetch('{$siteUrl}/api/get_houses.php?village_id=' + villageId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    houseSelect.innerHTML = '<option value="">Selecione uma casa</option>';
                    
                    if (data.error) {
                        console.error('Erro:', data.error);
                        houseSelect.innerHTML = '<option value="">Erro: ' + data.error + '</option>';
                        return;
                    }
                    
                    if (data.length === 0) {
                        houseSelect.innerHTML = '<option value="">Nenhuma casa cadastrada nesta village</option>';
                        return;
                    }
                    
                    data.forEach(house => {
                        const option = document.createElement('option');
                        option.value = house.id;
                        option.textContent = house.house_number + (house.complement ? ' - ' + house.complement : '');
                        houseSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erro:', error);
                    houseSelect.innerHTML = '<option value="">Erro ao carregar casas</option>';
                    alert('Erro ao carregar casas. Verifique o console para mais detalhes.');
                });
        } else {
            houseSelect.innerHTML = '<option value="">Selecione uma village primeiro</option>';
        }
    });
    
    // Quando selecionar casa, carregar condôminos
    document.getElementById('house_id').addEventListener('change', function() {
        const houseId = this.value;
        const residentSelect = document.getElementById('resident_id');
        
        residentSelect.innerHTML = '<option value="">Carregando...</option>';
        
        if (houseId) {
            fetch('{$siteUrl}/api/get_residents.php?house_id=' + houseId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    residentSelect.innerHTML = '<option value="">Selecione um condômino</option>';
                    
                    if (data.error) {
                        console.error('Erro:', data.error);
                        residentSelect.innerHTML = '<option value="">Erro: ' + data.error + '</option>';
                        return;
                    }
                    
                    if (data.length === 0) {
                        residentSelect.innerHTML = '<option value="">Nenhum condômino cadastrado nesta casa</option>';
                        return;
                    }
                    
                    data.forEach(resident => {
                        const option = document.createElement('option');
                        option.value = resident.id;
                        option.textContent = resident.full_name + ' - ' + resident.cpf;
                        residentSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erro:', error);
                    residentSelect.innerHTML = '<option value="">Erro ao carregar condôminos</option>';
                    alert('Erro ao carregar condôminos. Verifique o console para mais detalhes.');
                });
        } else {
            residentSelect.innerHTML = '<option value="">Selecione uma casa primeiro</option>';
        }
    });
});
</script>
SCRIPT;

require_once '../app/views/layouts/footer.php';
?>
