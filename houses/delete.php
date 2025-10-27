<?php
/**
 * E-Condo Packages - Deletar Casa
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
$house = $houseModel->findById($houseId);

if (!$house) {
    setFlash('danger', 'Casa não encontrada.');
    redirect('/houses/list.php');
}

// Verificar se tem condôminos associados
$residentModel = new Resident();
$residentsCount = $residentModel->countByHouse($houseId);

if ($residentsCount > 0) {
    setFlash('danger', "Não é possível excluir esta casa pois existem {$residentsCount} condômino(s) associado(s).");
    redirect('/houses/list.php?village_id=' . $house['village_id']);
}

// Deletar
$result = $houseModel->delete($houseId);

if ($result) {
    // Registrar log
    $logModel = new SystemLog();
    $logModel->insert([
        'user_id' => $_SESSION['user_id'],
        'action' => 'house_deleted',
        'entity_type' => 'house',
        'entity_id' => $houseId,
        'description' => "Casa '{$house['house_number']}' excluída",
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    
    setFlash('success', 'Casa excluída com sucesso!');
} else {
    setFlash('danger', 'Erro ao excluir casa.');
}

redirect('/houses/list.php?village_id=' . $house['village_id']);
