<?php
/**
 * E-Condo Packages - Deletar Condômino
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
$resident = $residentModel->findById($residentId);

if (!$resident) {
    setFlash('danger', 'Condômino não encontrado.');
    redirect('/residents/list.php');
}

// Verificar se tem encomendas associadas
$packageModel = new Package();
$packages = $packageModel->search(['resident_id' => $residentId]);

if (count($packages) > 0) {
    setFlash('danger', "Não é possível excluir este condômino pois existem " . count($packages) . " encomenda(s) associada(s).");
    redirect('/residents/list.php');
}

// Deletar
$result = $residentModel->delete($residentId);

if ($result) {
    // Registrar log
    $logModel = new SystemLog();
    $logModel->insert([
        'user_id' => $_SESSION['user_id'],
        'action' => 'resident_deleted',
        'entity_type' => 'resident',
        'entity_id' => $residentId,
        'description' => "Condômino '{$resident['full_name']}' excluído",
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    
    setFlash('success', 'Condômino excluído com sucesso!');
} else {
    setFlash('danger', 'Erro ao excluir condômino.');
}

redirect('/residents/list.php');
