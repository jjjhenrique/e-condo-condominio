<?php
/**
 * E-Condo Packages - Editar Condômino
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

$residentId = $_GET['id'] ?? '';

if (empty($residentId)) {
    setFlash('danger', 'Condômino não encontrado.');
    redirect('/residents/list.php');
}

$residentModel = new Resident();
$villageModel = new Village();
$houseModel = new House();

$resident = $residentModel->getByIdWithHouseInfo($residentId);

if (!$resident) {
    setFlash('danger', 'Condômino não encontrado.');
    redirect('/residents/list.php');
}

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
        // Verificar se CPF já existe (exceto este condômino)
        $existingResident = $residentModel->findByCPF($cpf);
        if ($existingResident && $existingResident['id'] != $residentId) {
            $errors[] = 'Este CPF já está cadastrado para outro condômino.';
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
        
        $result = $residentModel->update($residentId, $data);
        
        if ($result) {
            // Registrar log
            $logModel = new SystemLog();
            $logModel->insert([
                'user_id' => $_SESSION['user_id'],
                'action' => 'resident_updated',
                'entity_type' => 'resident',
                'entity_id' => $residentId,
                'description' => "Condômino '{$fullName}' atualizado",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            setFlash('success', 'Condômino atualizado com sucesso!');
            redirect('/residents/list.php');
        } else {
            $errors[] = 'Erro ao atualizar condômino.';
        }
    }
} else {
    // Preencher com dados atuais
    $_POST = $resident;
}

$villages = $villageModel->getActive();

$pageTitle = 'Editar Condômino';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-pencil"></i> Editar Condômino
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
                                        <option value="<?= $village['id'] ?>" 
                                                <?= ($resident['village_id'] ?? '') == $village['id'] ? 'selected' : '' ?>>
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
                                    <option value="">Carregando...</option>
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
                                    <option value="ativo" <?= ($_POST['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                    <option value="inativo" <?= ($_POST['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Atualizar Condômino
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Encomendas</h5>
                </div>
                <div class="card-body">
                    <?php
                    $packageModel = new Package();
                    $packages = $packageModel->search(['resident_id' => $residentId]);
                    ?>
                    
                    <p><strong>Total de encomendas:</strong> <?= count($packages) ?></p>
                    
                    <?php if (count($packages) > 0): ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach (array_slice($packages, 0, 10) as $pkg): ?>
                                <div class="border-bottom py-2">
                                    <small>
                                        <strong><?= htmlspecialchars($pkg['tracking_code']) ?></strong><br>
                                        <?= formatDateBR($pkg['received_at']) ?><br>
                                        <span class="badge bg-<?= $pkg['status'] === 'retirada' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($pkg['status']) ?>
                                        </span>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nenhuma encomenda registrada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$siteUrl = SITE_URL;
$currentVillageId = $resident['village_id'] ?? '';
$currentHouseId = $resident['house_id'] ?? '';
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
                    if ('{$currentHouseId}' && house.id == '{$currentHouseId}') {
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

// Carregar casas da village atual ao carregar a página
if ('{$currentVillageId}') {
    document.getElementById('village_id').dispatchEvent(new Event('change'));
}
</script>
SCRIPT;

require_once '../app/views/layouts/footer.php';
?>
