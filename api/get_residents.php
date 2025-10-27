<?php
/**
 * API - Buscar condôminos por casa
 */

require_once '../config/config.php';

header('Content-Type: application/json');

try {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autorizado']);
        exit;
    }

    $houseId = $_GET['house_id'] ?? '';

    if (empty($houseId)) {
        http_response_code(400);
        echo json_encode(['error' => 'House ID não fornecido']);
        exit;
    }

    $residentModel = new Resident();
    $residents = $residentModel->getByHouse($houseId);

    // Se não houver condôminos, retornar array vazio
    if (empty($residents)) {
        echo json_encode([]);
    } else {
        echo json_encode($residents);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar condôminos: ' . $e->getMessage()]);
}
