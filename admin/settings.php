<?php
/**
 * E-Condo Packages - Configurações do Sistema
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

$settingModel = new SystemSetting();
$whatsappService = new WhatsAppService();

$errors = [];
$success = false;

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'whatsapp') {
        $apiKey = sanitize($_POST['evolution_api_key'] ?? '');
        $instanceName = sanitize($_POST['evolution_instance_name'] ?? '');
        $apiUrl = sanitize($_POST['evolution_api_url'] ?? '');
        $enabled = isset($_POST['whatsapp_enabled']) ? '1' : '0';
        
        $settingModel->set('evolution_api_key', $apiKey);
        $settingModel->set('evolution_instance_name', $instanceName);
        $settingModel->set('evolution_api_url', $apiUrl);
        $settingModel->set('whatsapp_enabled', $enabled);
        
        $success = true;
        setFlash('success', 'Configurações da Evolution API atualizadas com sucesso!');
        
        // Registrar log
        $logModel = new SystemLog();
        $logModel->insert([
            'user_id' => $_SESSION['user_id'],
            'action' => 'settings_updated',
            'entity_type' => 'settings',
            'description' => 'Configurações da Evolution API atualizadas',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } elseif ($action === 'test_whatsapp') {
        $testPhone = sanitize($_POST['test_phone'] ?? '');
        
        if (empty($testPhone)) {
            $errors[] = 'Digite um número de telefone para teste.';
        } else {
            $result = $whatsappService->testConnection($testPhone);
            
            if ($result['success']) {
                setFlash('success', 'Mensagem de teste enviada com sucesso!');
            } else {
                setFlash('danger', 'Erro ao enviar mensagem: ' . ($result['error'] ?? 'Erro desconhecido'));
            }
        }
    }
}

// Carregar configurações atuais
$whatsappConfig = $settingModel->getWhatsAppConfig();

$pageTitle = 'Configurações';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-gear"></i> Configurações do Sistema
                </h1>
                <a href="<?= SITE_URL ?>/index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Configurações WhatsApp -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-whatsapp"></i> Configurações da Evolution API</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="whatsapp">
                        
                        <div class="mb-3">
                            <label for="evolution_api_url" class="form-label">
                                <i class="bi bi-link-45deg"></i> URL da Evolution API *
                            </label>
                            <input type="text" class="form-control" id="evolution_api_url" name="evolution_api_url" 
                                   value="<?= htmlspecialchars($settingModel->getEvolutionApiUrl()) ?>" 
                                   placeholder="http://localhost:8080">
                            <small class="text-muted">
                                URL base da Evolution API (sem barra no final)
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="evolution_api_key" class="form-label">
                                <i class="bi bi-key"></i> API Key *
                            </label>
                            <input type="text" class="form-control" id="evolution_api_key" name="evolution_api_key" 
                                   value="<?= htmlspecialchars($whatsappConfig['api_key']) ?>" 
                                   placeholder="Cole aqui a API Key da Evolution API">
                            <small class="text-muted">
                                API Key global configurada na Evolution API
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="evolution_instance_name" class="form-label">
                                <i class="bi bi-phone"></i> Nome da Instância *
                            </label>
                            <input type="text" class="form-control" id="evolution_instance_name" name="evolution_instance_name" 
                                   value="<?= htmlspecialchars($whatsappConfig['instance_name']) ?>" 
                                   placeholder="Ex: econdo">
                            <small class="text-muted">
                                Nome da instância criada na Evolution API
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="whatsapp_enabled" 
                                       name="whatsapp_enabled" <?= $whatsappConfig['enabled'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="whatsapp_enabled">
                                    Habilitar envio de notificações via WhatsApp
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Testar WhatsApp -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Testar Conexão WhatsApp</h5>
                </div>
                <div class="card-body">
                    <p>Envie uma mensagem de teste para verificar se a integração está funcionando corretamente.</p>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="test_whatsapp">
                        
                        <div class="mb-3">
                            <label for="test_phone" class="form-label">
                                <i class="bi bi-telephone"></i> Número de Telefone (com DDD)
                            </label>
                            <input type="text" class="form-control" id="test_phone" name="test_phone" 
                                   placeholder="Ex: 11987654321" data-format="phone" required>
                            <small class="text-muted">Digite apenas números com DDD</small>
                        </div>
                        
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-send"></i> Enviar Mensagem de Teste
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Status da Integração -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Status da Integração</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>WhatsApp API:</strong><br>
                        <?php if ($whatsappService->isEnabled()): ?>
                            <span class="badge bg-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Inativo</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong>API Key Configurada:</strong><br>
                        <?php if (!empty($whatsappConfig['api_key'])): ?>
                            <i class="bi bi-check-circle text-success"></i> Sim
                        <?php else: ?>
                            <i class="bi bi-x-circle text-danger"></i> Não
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Instância Configurada:</strong><br>
                        <?php if (!empty($whatsappConfig['instance_name'])): ?>
                            <i class="bi bi-check-circle text-success"></i> Sim (<?= htmlspecialchars($whatsappConfig['instance_name']) ?>)
                        <?php else: ?>
                            <i class="bi bi-x-circle text-danger"></i> Não
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong>URL da API:</strong><br>
                        <small class="text-muted"><?= htmlspecialchars($settingModel->getEvolutionApiUrl()) ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Instruções -->
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-book"></i> Como Configurar</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Passo a Passo:</h6>
                    <ol class="small">
                        <li>Instale a Evolution API (Docker recomendado)</li>
                        <li>Acesse a Evolution API e anote a <strong>API Key</strong></li>
                        <li>Crie uma instância (ex: "econdo")</li>
                        <li>Conecte o WhatsApp escaneando o QR Code</li>
                        <li>Cole a <strong>URL da API</strong> no campo acima</li>
                        <li>Cole a <strong>API Key</strong> no campo acima</li>
                        <li>Cole o <strong>Nome da Instância</strong> no campo acima</li>
                        <li>Habilite o envio de notificações</li>
                        <li>Clique em "Salvar"</li>
                        <li>Teste a conexão</li>
                    </ol>
                    
                    <hr>
                    
                    <div class="alert alert-info mb-2 small">
                        <i class="bi bi-info-circle"></i>
                        <strong>Documentação:</strong> <a href="https://doc.evolution-api.com/" target="_blank">Evolution API Docs</a>
                    </div>
                    
                    <div class="alert alert-warning mb-0 small">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Importante:</strong> Mantenha a API Key em segurança. Não compartilhe com terceiros.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
