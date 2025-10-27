<?php
/**
 * E-Condo Packages - Logout
 */

require_once 'config/config.php';

// Registrar log de logout
if (isLoggedIn()) {
    $logModel = new SystemLog();
    $logModel->insert([
        'user_id' => $_SESSION['user_id'],
        'action' => 'logout',
        'description' => 'Usuário realizou logout do sistema',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

// Destruir sessão
session_unset();
session_destroy();

setFlash('success', 'Você saiu do sistema com sucesso.');
redirect('/login.php');
