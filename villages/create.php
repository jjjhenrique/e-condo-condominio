<?php
/**
 * E-Condo Packages - Criar Village
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

$villageModel = new Village();
$errors = [];
$success = false;

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'ativo';
    
    // Validação
    if (empty($name)) {
        $errors[] = 'O nome da village é obrigatório.';
    }
    
    if (empty($errors)) {
        $data = [
            'name' => $name,
            'description' => $description,
            'status' => $status
        ];
        
        $villageId = $villageModel->insert($data);
        
        if ($villageId) {
            // Registrar log
            $logModel = new SystemLog();
            $logModel->insert([
                'user_id' => $_SESSION['user_id'],
                'action' => 'village_created',
                'entity_type' => 'village',
                'entity_id' => $villageId,
                'description' => "Village '{$name}' criada",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            setFlash('success', 'Village cadastrada com sucesso!');
            redirect('/villages/list.php');
        } else {
            $errors[] = 'Erro ao cadastrar village.';
        }
    }
}

$pageTitle = 'Nova Village';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-plus-circle"></i> Nova Village
                </h1>
                <a href="<?= SITE_URL ?>/villages/list.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-buildings"></i> Dados da Village</h5>
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
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-buildings"></i> Nome da Village *
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                                   placeholder="Ex: Village A, Bloco 1, etc." required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="bi bi-chat-left-text"></i> Descrição
                            </label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3" placeholder="Informações adicionais sobre a village (opcional)"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="bi bi-toggle-on"></i> Status
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="ativo" <?= ($_POST['status'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="inativo" <?= ($_POST['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Cadastrar Village
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
                    <h6 class="fw-bold">O que é uma Village?</h6>
                    <p class="small">
                        Uma village é um agrupamento de casas/unidades dentro do condomínio. 
                        Pode ser um bloco, uma rua, ou qualquer divisão lógica do seu condomínio.
                    </p>
                    
                    <hr>
                    
                    <h6 class="fw-bold">Próximos passos:</h6>
                    <ol class="small">
                        <li>Cadastre a village</li>
                        <li>Adicione as casas desta village</li>
                        <li>Cadastre os condôminos</li>
                        <li>Comece a registrar encomendas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
