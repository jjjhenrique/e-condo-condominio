<?php
/**
 * E-Condo Packages - Deletar Village
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

$villageId = $_GET['id'] ?? '';

if (empty($villageId)) {
    setFlash('danger', 'Village não encontrada.');
    redirect('/villages/list.php');
}

$villageModel = new Village();
$village = $villageModel->findById($villageId);

if (!$village) {
    setFlash('danger', 'Village não encontrada.');
    redirect('/villages/list.php');
}

// Verificar se tem casas associadas
$houseModel = new House();
$housesCount = $houseModel->countByVillage($villageId);

if ($housesCount > 0) {
    setFlash('danger', "Não é possível excluir esta village pois existem {$housesCount} casa(s) associada(s).");
    redirect('/villages/list.php');
}

// Deletar
$result = $villageModel->delete($villageId);

if ($result) {
    // Registrar log
    $logModel = new SystemLog();
    $logModel->insert([
        'user_id' => $_SESSION['user_id'],
        'action' => 'village_deleted',
        'entity_type' => 'village',
        'entity_id' => $villageId,
        'description' => "Village '{$village['name']}' excluída",
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    
    setFlash('success', 'Village excluída com sucesso!');
} else {
    setFlash('danger', 'Erro ao excluir village.');
}

redirect('/villages/list.php');
