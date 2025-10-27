<?php
/**
 * E-Condo Packages - Editar Usuário
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

$userId = $_GET['id'] ?? '';

if (empty($userId)) {
    setFlash('danger', 'Usuário não encontrado.');
    redirect('/admin/users.php');
}

$userModel = new User();
$user = $userModel->findById($userId);

if (!$user) {
    setFlash('danger', 'Usuário não encontrado.');
    redirect('/admin/users.php');
}

$errors = [];

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? 'ativo';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    // Validação
    if (empty($username)) {
        $errors[] = 'O nome de usuário é obrigatório.';
    }
    
    if (empty($fullName)) {
        $errors[] = 'O nome completo é obrigatório.';
    }
    
    if (empty($email)) {
        $errors[] = 'O email é obrigatório.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido.';
    }
    
    if (empty($role)) {
        $errors[] = 'Selecione um perfil.';
    }
    
    // Verificar se username já existe (exceto este usuário)
    $existingUser = $userModel->findByUsername($username);
    if ($existingUser && $existingUser['id'] != $userId) {
        $errors[] = 'Este nome de usuário já está em uso.';
    }
    
    // Verificar se email já existe (exceto este usuário)
    $existingEmail = $userModel->findByEmail($email);
    if ($existingEmail && $existingEmail['id'] != $userId) {
        $errors[] = 'Este email já está em uso.';
    }
    
    // Validar senha se foi fornecida
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'A senha deve ter no mínimo 6 caracteres.';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = 'As senhas não conferem.';
        }
    }
    
    if (empty($errors)) {
        $data = [
            'username' => $username,
            'full_name' => $fullName,
            'email' => $email,
            'role' => $role,
            'status' => $status
        ];
        
        // Adicionar senha se foi fornecida
        if (!empty($password)) {
            $data['password'] = $password;
        }
        
        $result = $userModel->updateUser($userId, $data);
        
        if ($result) {
            // Registrar log
            $logModel = new SystemLog();
            $logModel->insert([
                'user_id' => $_SESSION['user_id'],
                'action' => 'user_updated',
                'entity_type' => 'user',
                'entity_id' => $userId,
                'description' => "Usuário '{$username}' atualizado",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            setFlash('success', 'Usuário atualizado com sucesso!');
            redirect('/admin/users.php');
        } else {
            $errors[] = 'Erro ao atualizar usuário.';
        }
    }
} else {
    // Preencher com dados atuais
    $_POST = $user;
}

$pageTitle = 'Editar Usuário';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-pencil"></i> Editar Usuário
                </h1>
                <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Dados do Usuário</h5>
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
                                <label for="username" class="form-label">
                                    <i class="bi bi-person-badge"></i> Nome de Usuário *
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                                       placeholder="Ex: admin, porteiro1" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="bi bi-person"></i> Nome Completo *
                                </label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" 
                                       placeholder="Nome completo do usuário" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                       placeholder="email@exemplo.com" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">
                                    <i class="bi bi-shield"></i> Perfil *
                                </label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Selecione um perfil</option>
                                    <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                    <option value="porteiro" <?= ($_POST['role'] ?? '') === 'porteiro' ? 'selected' : '' ?>>Porteiro</option>
                                    <option value="administracao" <?= ($_POST['role'] ?? '') === 'administracao' ? 'selected' : '' ?>>Administração</option>
                                </select>
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
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">
                            <i class="bi bi-key"></i> Alterar Senha
                            <small class="text-muted">(deixe em branco para manter a senha atual)</small>
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Mínimo 6 caracteres">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                       placeholder="Digite a senha novamente">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Atualizar Usuário
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
                    <h6 class="fw-bold">Perfis de Usuário:</h6>
                    <ul class="small">
                        <li><strong>Administrador:</strong> Acesso total ao sistema</li>
                        <li><strong>Porteiro:</strong> Receber e transferir encomendas</li>
                        <li><strong>Administração:</strong> Retirar encomendas e relatórios</li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="fw-bold">Segurança:</h6>
                    <ul class="small">
                        <li>Use senhas fortes (mínimo 6 caracteres)</li>
                        <li>Não compartilhe credenciais</li>
                        <li>Desative usuários inativos</li>
                    </ul>
                </div>
            </div>
            
            <div class="card border-warning mt-3">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Atenção</h5>
                </div>
                <div class="card-body">
                    <p class="small mb-0">
                        Ao alterar o perfil ou status de um usuário, as mudanças terão efeito imediato.
                        Usuários inativos não poderão fazer login no sistema.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
