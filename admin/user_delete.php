<?php
/**
 * E-Condo Packages - Deletar Usuário
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

// Não permitir deletar o próprio usuário
if ($userId == $_SESSION['user_id']) {
    setFlash('danger', 'Você não pode excluir seu próprio usuário.');
    redirect('/admin/users.php');
}

$userModel = new User();
$user = $userModel->findById($userId);

if (!$user) {
    setFlash('danger', 'Usuário não encontrado.');
    redirect('/admin/users.php');
}

// Deletar
$result = $userModel->delete($userId);

if ($result) {
    // Registrar log
    $logModel = new SystemLog();
    $logModel->insert([
        'user_id' => $_SESSION['user_id'],
        'action' => 'user_deleted',
        'entity_type' => 'user',
        'entity_id' => $userId,
        'description' => "Usuário '{$user['username']}' excluído",
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    
    setFlash('success', 'Usuário excluído com sucesso!');
} else {
    setFlash('danger', 'Erro ao excluir usuário.');
}

redirect('/admin/users.php');
