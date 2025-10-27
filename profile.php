<?php
/**
 * E-Condo Packages - Meu Perfil
 */

require_once 'config/config.php';

// Verificar autenticação
if (!isLoggedIn()) {
    redirect('/login.php');
}

$userModel = new User();
$userId = $_SESSION['user_id'];
$user = $userModel->findById($userId);

if (!$user) {
    setFlash('danger', 'Usuário não encontrado.');
    redirect('/index.php');
}

$errors = [];
$success = false;

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validação
    if (empty($fullName)) {
        $errors[] = 'O nome completo é obrigatório.';
    }
    
    if (empty($email)) {
        $errors[] = 'O email é obrigatório.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido.';
    }
    
    // Verificar se email já existe (exceto o próprio)
    $existingEmail = $userModel->findByEmail($email);
    if ($existingEmail && $existingEmail['id'] != $userId) {
        $errors[] = 'Este email já está em uso por outro usuário.';
    }
    
    // Se está tentando alterar a senha
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'Digite sua senha atual para alterar a senha.';
        } elseif (!$userModel->verifyPassword($userId, $currentPassword)) {
            $errors[] = 'Senha atual incorreta.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'A nova senha deve ter no mínimo 6 caracteres.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'A confirmação da nova senha não confere.';
        }
    }
    
    if (empty($errors)) {
        $data = [
            'full_name' => $fullName,
            'email' => $email
        ];
        
        // Adicionar nova senha se foi fornecida
        if (!empty($newPassword)) {
            $data['password'] = $newPassword;
        }
        
        $result = $userModel->updateUser($userId, $data);
        
        if ($result) {
            // Atualizar nome na sessão
            $_SESSION['user_name'] = $fullName;
            
            // Registrar log
            $logModel = new SystemLog();
            $logModel->insert([
                'user_id' => $userId,
                'action' => 'profile_updated',
                'entity_type' => 'user',
                'entity_id' => $userId,
                'description' => "Perfil atualizado",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            $success = true;
            setFlash('success', 'Perfil atualizado com sucesso!');
            
            // Recarregar dados do usuário
            $user = $userModel->findById($userId);
        } else {
            $errors[] = 'Erro ao atualizar perfil.';
        }
    }
}

$pageTitle = 'Meu Perfil';
require_once 'app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-person-circle"></i> Meu Perfil
                </h1>
                <a href="<?= SITE_URL ?>/index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Dados Pessoais</h5>
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
                            <i class="bi bi-check-circle"></i> Perfil atualizado com sucesso!
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person-badge"></i> Nome de Usuário
                            </label>
                            <input type="text" class="form-control" id="username" 
                                   value="<?= htmlspecialchars($user['username']) ?>" 
                                   disabled>
                            <small class="text-muted">O nome de usuário não pode ser alterado</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">
                                <i class="bi bi-person"></i> Nome Completo *
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?= htmlspecialchars($user['full_name']) ?>" 
                                   placeholder="Seu nome completo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> Email *
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" 
                                   placeholder="seu@email.com" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">
                                <i class="bi bi-shield"></i> Perfil
                            </label>
                            <input type="text" class="form-control" id="role" 
                                   value="<?= ucfirst($user['role']) ?>" disabled>
                            <small class="text-muted">O perfil é definido pelo administrador</small>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">
                            <i class="bi bi-key"></i> Alterar Senha
                            <small class="text-muted">(deixe em branco para manter a senha atual)</small>
                        </h6>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Senha Atual</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" 
                                   placeholder="Digite sua senha atual">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       placeholder="Mínimo 6 caracteres">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Digite a senha novamente">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações da Conta</h5>
                </div>
                <div class="card-body">
                    <p><strong>Usuário:</strong> <?= htmlspecialchars($user['username']) ?></p>
                    <p><strong>Perfil:</strong> 
                        <?php
                        $roleBadge = [
                            'admin' => '<span class="badge bg-danger">Administrador</span>',
                            'porteiro' => '<span class="badge bg-primary">Porteiro</span>',
                            'administracao' => '<span class="badge bg-info">Administração</span>'
                        ];
                        echo $roleBadge[$user['role']] ?? '';
                        ?>
                    </p>
                    <p><strong>Status:</strong> 
                        <?php if ($user['status'] === 'ativo'): ?>
                            <span class="badge bg-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inativo</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Último acesso:</strong><br>
                        <?= isset($user['last_login']) && $user['last_login'] ? formatDateBR($user['last_login']) : 'Nunca' ?>
                    </p>
                    <p><strong>Conta criada em:</strong><br>
                        <?= formatDateBR($user['created_at']) ?>
                    </p>
                </div>
            </div>
            
            <div class="card border-warning mt-3">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Segurança</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Dicas de Segurança:</h6>
                    <ul class="small">
                        <li>Use uma senha forte e única</li>
                        <li>Não compartilhe suas credenciais</li>
                        <li>Altere sua senha periodicamente</li>
                        <li>Faça logout ao sair</li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="fw-bold">Requisitos de Senha:</h6>
                    <ul class="small">
                        <li>Mínimo de 6 caracteres</li>
                        <li>Recomendado: letras, números e símbolos</li>
                    </ul>
                </div>
            </div>
            
            <?php
            // Estatísticas do usuário
            $logModel = new SystemLog();
            $userLogs = $logModel->getByUser($userId, 10);
            ?>
            
            <div class="card border-secondary mt-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Atividades Recentes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($userLogs)): ?>
                        <p class="text-muted small mb-0">Nenhuma atividade registrada.</p>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($userLogs as $log): ?>
                                <div class="border-bottom py-2">
                                    <small>
                                        <strong><?= htmlspecialchars($log['description']) ?></strong><br>
                                        <span class="text-muted"><?= formatDateBR($log['created_at']) ?></span>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/layouts/footer.php'; ?>
