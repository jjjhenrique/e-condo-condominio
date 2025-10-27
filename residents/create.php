<?php
/**
 * E-Condo Packages - Criar Condômino
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

$residentModel = new Resident();
$villageModel = new Village();
$houseModel = new House();

$preSelectedHouse = $_GET['house_id'] ?? '';
$errors = [];

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $houseId = $_POST['house_id'] ?? '';
    $fullName = sanitize($_POST['full_name'] ?? '');
    $cpf = sanitize($_POST['cpf'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'ativo';
    
    // Validação
    if (empty($houseId)) {
        $errors[] = 'Selecione uma casa.';
    }
    
    if (empty($fullName)) {
        $errors[] = 'O nome completo é obrigatório.';
    }
    
    if (empty($cpf)) {
        $errors[] = 'O CPF é obrigatório.';
    } else {
        // Verificar se CPF já existe
        if ($residentModel->cpfExists($cpf)) {
            $errors[] = 'Este CPF já está cadastrado.';
        }
    }
    
    if (empty($phone)) {
        $errors[] = 'O telefone é obrigatório.';
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido.';
    }
    
    if (empty($errors)) {
        $data = [
            'house_id' => $houseId,
            'full_name' => $fullName,
            'cpf' => $cpf,
            'phone' => $phone,
            'email' => $email,
            'status' => $status
        ];
        
        $residentId = $residentModel->insert($data);
        
        if ($residentId) {
            // Registrar log
            $logModel = new SystemLog();
            $logModel->insert([
                'user_id' => $_SESSION['user_id'],
                'action' => 'resident_created',
                'entity_type' => 'resident',
                'entity_id' => $residentId,
                'description' => "Condômino '{$fullName}' criado",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            setFlash('success', 'Condômino cadastrado com sucesso!');
            redirect('/residents/list.php');
        } else {
            $errors[] = 'Erro ao cadastrar condômino.';
        }
    }
}

$villages = $villageModel->getActive();

$pageTitle = 'Novo Condômino';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-plus-circle"></i> Novo Condômino
                </h1>
                <a href="<?= SITE_URL ?>/residents/list.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Dados do Condômino</h5>
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="village_id" class="form-label">
                                    <i class="bi bi-buildings"></i> Village *
                                </label>
                                <select class="form-select" id="village_id" name="village_id" required>
                                    <option value="">Selecione uma village</option>
                                    <?php foreach ($villages as $village): ?>
                                        <option value="<?= $village['id'] ?>">
                                            <?= htmlspecialchars($village['name']) ?>
                                        </option>
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
                            <label for="full_name" class="form-label">
                                <i class="bi bi-person"></i> Nome Completo *
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" 
                                   placeholder="Nome completo do condômino" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cpf" class="form-label">
                                    <i class="bi bi-card-text"></i> CPF *
                                </label>
                                <input type="text" class="form-control" id="cpf" name="cpf" 
                                       value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>" 
                                       placeholder="000.000.000-00" required maxlength="14">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">
                                    <i class="bi bi-phone"></i> Telefone/WhatsApp *
                                </label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" 
                                       placeholder="(00) 00000-0000" required maxlength="15">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                       placeholder="email@exemplo.com">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">
                                    <i class="bi bi-toggle-on"></i> Status
                                </label>
                                <select class="form-select" id="status" name="status">
                                    <option value="ativo" <?= ($_POST['status'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                    <option value="inativo" <?= ($_POST['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Cadastrar Condômino
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
                    <h6 class="fw-bold">Dicas:</h6>
                    <ul class="small">
                        <li>O CPF deve ser único no sistema</li>
                        <li>O telefone será usado para notificações WhatsApp</li>
                        <li>Use o formato (00) 00000-0000 para telefone</li>
                        <li>O email é opcional</li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="fw-bold">Formato dos Campos:</h6>
                    <ul class="small">
                        <li><strong>CPF:</strong> 000.000.000-00</li>
                        <li><strong>Telefone:</strong> (00) 00000-0000</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$siteUrl = SITE_URL;
$preSelectedHouseId = $preSelectedHouse;
$extraScripts = <<<SCRIPT
<script>
// Máscaras para CPF e Telefone
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '\$1.\$2');
        value = value.replace(/(\d{3})(\d)/, '\$1.\$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '\$1-\$2');
        e.target.value = value;
    }
});

document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{2})(\d)/, '(\$1) \$2');
        value = value.replace(/(\d{5})(\d)/, '\$1-\$2');
        e.target.value = value;
    }
});

// Carregar casas quando selecionar village
document.getElementById('village_id').addEventListener('change', function() {
    const villageId = this.value;
    const houseSelect = document.getElementById('house_id');
    
    houseSelect.innerHTML = '<option value="">Carregando...</option>';
    
    if (villageId) {
        fetch('{$siteUrl}/api/get_houses.php?village_id=' + villageId)
            .then(response => response.json())
            .then(data => {
                houseSelect.innerHTML = '<option value="">Selecione uma casa</option>';
                
                if (data.length === 0) {
                    houseSelect.innerHTML = '<option value="">Nenhuma casa cadastrada</option>';
                    return;
                }
                
                data.forEach(house => {
                    const option = document.createElement('option');
                    option.value = house.id;
                    option.textContent = house.house_number + (house.complement ? ' - ' + house.complement : '');
                    if ('{$preSelectedHouseId}' && house.id == '{$preSelectedHouseId}') {
                        option.selected = true;
                    }
                    houseSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Erro:', error);
                houseSelect.innerHTML = '<option value="">Erro ao carregar casas</option>';
            });
    } else {
        houseSelect.innerHTML = '<option value="">Selecione uma village primeiro</option>';
    }
});
</script>
SCRIPT;

require_once '../app/views/layouts/footer.php';
?>
