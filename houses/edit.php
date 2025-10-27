<?php
/**
 * E-Condo Packages - Editar Casa
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

$houseId = $_GET['id'] ?? '';

if (empty($houseId)) {
    setFlash('danger', 'Casa não encontrada.');
    redirect('/houses/list.php');
}

$houseModel = new House();
$villageModel = new Village();

$house = $houseModel->findById($houseId);

if (!$house) {
    setFlash('danger', 'Casa não encontrada.');
    redirect('/houses/list.php');
}

$errors = [];

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $villageId = $_POST['village_id'] ?? '';
    $houseNumber = sanitize($_POST['house_number'] ?? '');
    $complement = sanitize($_POST['complement'] ?? '');
    $status = $_POST['status'] ?? 'ativo';
    
    // Validação
    if (empty($villageId)) {
        $errors[] = 'Selecione uma village.';
    }
    
    if (empty($houseNumber)) {
        $errors[] = 'O número da casa é obrigatório.';
    }
    
    // Verificar se já existe (exceto esta casa)
    if (!empty($villageId) && !empty($houseNumber)) {
        $existing = $houseModel->findByVillageAndNumber($villageId, $houseNumber);
        if ($existing && $existing['id'] != $houseId) {
            $errors[] = 'Já existe uma casa com este número nesta village.';
        }
    }
    
    if (empty($errors)) {
        $data = [
            'village_id' => $villageId,
            'house_number' => $houseNumber,
            'complement' => $complement,
            'status' => $status
        ];
        
        $result = $houseModel->update($houseId, $data);
        
        if ($result) {
            // Registrar log
            $logModel = new SystemLog();
            $logModel->insert([
                'user_id' => $_SESSION['user_id'],
                'action' => 'house_updated',
                'entity_type' => 'house',
                'entity_id' => $houseId,
                'description' => "Casa '{$houseNumber}' atualizada",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            setFlash('success', 'Casa atualizada com sucesso!');
            redirect('/houses/list.php?village_id=' . $villageId);
        } else {
            $errors[] = 'Erro ao atualizar casa.';
        }
    }
} else {
    // Preencher com dados atuais
    $_POST = $house;
}

$villages = $villageModel->getActive();

$pageTitle = 'Editar Casa';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-pencil"></i> Editar Casa
                </h1>
                <a href="<?= SITE_URL ?>/houses/list.php?village_id=<?= $house['village_id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-house"></i> Dados da Casa</h5>
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
                            <label for="village_id" class="form-label">
                                <i class="bi bi-buildings"></i> Village *
                            </label>
                            <select class="form-select" id="village_id" name="village_id" required>
                                <option value="">Selecione uma village</option>
                                <?php foreach ($villages as $village): ?>
                                    <option value="<?= $village['id'] ?>" 
                                            <?= ($_POST['village_id'] ?? '') == $village['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($village['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="house_number" class="form-label">
                                    <i class="bi bi-house"></i> Número da Casa *
                                </label>
                                <input type="text" class="form-control" id="house_number" name="house_number" 
                                       value="<?= htmlspecialchars($_POST['house_number'] ?? '') ?>" 
                                       placeholder="Ex: 101, A1, etc." required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="complement" class="form-label">
                                    <i class="bi bi-info-circle"></i> Complemento
                                </label>
                                <input type="text" class="form-control" id="complement" name="complement" 
                                       value="<?= htmlspecialchars($_POST['complement'] ?? '') ?>" 
                                       placeholder="Ex: Apto 201, Fundos, etc.">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="bi bi-toggle-on"></i> Status
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="ativo" <?= ($_POST['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="inativo" <?= ($_POST['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Atualizar Casa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Condôminos desta Casa</h5>
                </div>
                <div class="card-body">
                    <?php
                    $residentModel = new Resident();
                    $residents = $residentModel->getByHouse($houseId);
                    ?>
                    
                    <p><strong>Total de condôminos:</strong> <?= count($residents) ?></p>
                    
                    <?php if (count($residents) > 0): ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($residents as $resident): ?>
                                <div class="border-bottom py-2">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($resident['full_name']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nenhum condômino cadastrado ainda.</p>
                    <?php endif; ?>
                    
                    <a href="<?= SITE_URL ?>/residents/create.php?house_id=<?= $houseId ?>" class="btn btn-sm btn-primary mt-3 w-100">
                        <i class="bi bi-plus-circle"></i> Adicionar Condômino
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
