<?php
/**
 * API - Buscar casas por village
 */

// Habilitar erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 0); // Não mostrar na tela, apenas logar

require_once '../config/config.php';

header('Content-Type: application/json');

try {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autorizado']);
        exit;
    }

    $villageId = $_GET['village_id'] ?? '';

    if (empty($villageId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Village ID não fornecido']);
        exit;
    }

    $houseModel = new House();
    $houses = $houseModel->getActiveByVillage($villageId);

    // Log para debug
    error_log("API get_houses.php - Village ID: {$villageId}, Casas encontradas: " . count($houses));

    // Se não houver casas, retornar array vazio
    if (empty($houses)) {
        echo json_encode([]);
    } else {
        echo json_encode($houses);
    }
    
} catch (Exception $e) {
    error_log("API get_houses.php - Erro: " . $e->getMessage());
    error_log("API get_houses.php - Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro ao buscar casas: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
