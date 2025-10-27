<?php
/**
 * E-Condo Packages - Reenviar Notificação WhatsApp
 */

require_once '../config/config.php';

// Verificar autenticação
if (!isLoggedIn()) {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/packages/list.php');
}

$notificationId = $_POST['notification_id'] ?? '';
$packageId = $_POST['package_id'] ?? '';

if (empty($notificationId) || empty($packageId)) {
    setFlash('danger', 'Dados inválidos.');
    redirect('/packages/view.php?id=' . $packageId);
}

$whatsappService = new WhatsAppService();

// Reenviar notificação
$result = $whatsappService->resendNotification($notificationId);

if ($result['success']) {
    setFlash('success', 'Notificação reenviada com sucesso!');
} else {
    $errorMsg = $result['error'] ?? 'Erro desconhecido';
    setFlash('danger', 'Erro ao reenviar notificação: ' . $errorMsg);
}

redirect('/packages/view.php?id=' . $packageId);
