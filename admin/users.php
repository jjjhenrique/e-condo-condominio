<?php
/**
 * E-Condo Packages - Gerenciar Usuários
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

$userModel = new User();
$users = $userModel->getAll();

$pageTitle = 'Usuários';
require_once '../app/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-people"></i> Usuários do Sistema
                </h1>
                <a href="<?= SITE_URL ?>/admin/user_create.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Novo Usuário
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list"></i> 
                        <?= count($users) ?> usuário(s) cadastrado(s)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuário</th>
                                    <th>Nome Completo</th>
                                    <th>Email</th>
                                    <th>Perfil</th>
                                    <th>Status</th>
                                    <th>Último Acesso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php
                                        $roleBadge = [
                                            'admin' => '<span class="badge bg-danger">Administrador</span>',
                                            'porteiro' => '<span class="badge bg-primary">Porteiro</span>',
                                            'administracao' => '<span class="badge bg-info">Administração</span>'
                                        ];
                                        echo $roleBadge[$user['role']] ?? '';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === 'ativo'): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= isset($user['last_login']) && $user['last_login'] ? formatDateBR($user['last_login']) : 'Nunca' ?></td>
                                    <td>
                                        <a href="<?= SITE_URL ?>/admin/user_edit.php?id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="<?= SITE_URL ?>/admin/user_delete.php?id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Tem certeza que deseja excluir este usuário?')"
                                           title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
