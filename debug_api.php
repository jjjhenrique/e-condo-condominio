<?php
/**
 * Debug das APIs - Testar diretamente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';

// Simular login se não estiver logado
if (!isLoggedIn()) {
    echo "<h1>❌ Você precisa estar logado</h1>";
    echo "<p><a href='login.php'>Fazer Login</a></p>";
    exit;
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Debug API - E-Condo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>🐛 Debug de APIs</h1>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5>Teste 1: Listar Villages</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $villageModel = new Village();
                    $villages = $villageModel->getAll();
                    
                    echo "<p><strong>Total:</strong> " . count($villages) . " village(s)</p>";
                    
                    if (empty($villages)) {
                        echo "<div class='alert alert-warning'>Nenhuma village encontrada</div>";
                    } else {
                        echo "<pre>" . print_r($villages, true) . "</pre>";
                    }
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
                    echo "<pre>" . $e->getTraceAsString() . "</pre>";
                }
                ?>
            </div>
        </div>

        <?php if (!empty($villages)): ?>
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h5>Teste 2: Buscar Casas por Village</h5>
            </div>
            <div class="card-body">
                <?php
                $villageId = $villages[0]['id'];
                echo "<p>Testando com Village ID: <strong>{$villageId}</strong> ({$villages[0]['name']})</p>";
                
                try {
                    $houseModel = new House();
                    
                    echo "<h6>Método: getActiveByVillage()</h6>";
                    $houses = $houseModel->getActiveByVillage($villageId);
                    
                    echo "<p><strong>Total:</strong> " . count($houses) . " casa(s)</p>";
                    
                    if (empty($houses)) {
                        echo "<div class='alert alert-warning'>Nenhuma casa encontrada</div>";
                    } else {
                        echo "<pre>" . print_r($houses, true) . "</pre>";
                    }
                    
                    echo "<hr>";
                    
                    echo "<h6>Teste direto da API:</h6>";
                    $apiUrl = SITE_URL . "/api/get_houses.php?village_id={$villageId}";
                    echo "<p>URL: <code>{$apiUrl}</code></p>";
                    
                    // Simular chamada da API
                    $_GET['village_id'] = $villageId;
                    ob_start();
                    include 'api/get_houses.php';
                    $apiResponse = ob_get_clean();
                    
                    echo "<p><strong>Resposta da API:</strong></p>";
                    echo "<pre>" . htmlspecialchars($apiResponse) . "</pre>";
                    
                    $decoded = json_decode($apiResponse, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        echo "<p><strong>JSON decodificado:</strong></p>";
                        echo "<pre>" . print_r($decoded, true) . "</pre>";
                    } else {
                        echo "<div class='alert alert-danger'>Erro ao decodificar JSON: " . json_last_error_msg() . "</div>";
                    }
                    
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
                    echo "<pre>" . $e->getTraceAsString() . "</pre>";
                }
                ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5>Teste 3: Verificar Estrutura da Tabela Houses</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $db = Database::getInstance();
                    $pdo = $db->getConnection();
                    
                    $result = $pdo->query("DESCRIBE houses");
                    $columns = $result->fetchAll();
                    
                    echo "<table class='table table-sm'>";
                    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
                    foreach ($columns as $col) {
                        echo "<tr>";
                        echo "<td><code>{$col['Field']}</code></td>";
                        echo "<td>{$col['Type']}</td>";
                        echo "<td>{$col['Null']}</td>";
                        echo "<td>{$col['Key']}</td>";
                        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    
                    // Contar casas
                    $result = $pdo->query("SELECT COUNT(*) as total FROM houses");
                    $count = $result->fetch();
                    echo "<p><strong>Total de casas no banco:</strong> {$count['total']}</p>";
                    
                    // Mostrar algumas casas
                    if ($count['total'] > 0) {
                        $result = $pdo->query("SELECT * FROM houses LIMIT 5");
                        $samples = $result->fetchAll();
                        echo "<p><strong>Exemplos de casas:</strong></p>";
                        echo "<pre>" . print_r($samples, true) . "</pre>";
                    }
                    
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
                }
                ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="test_api.php" class="btn btn-primary">Teste Completo</a>
            <a href="update_database.php" class="btn btn-warning">Atualizar Banco</a>
            <a href="packages/receive.php" class="btn btn-success">Receber Encomendas</a>
            <a href="index.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
</body>
</html>
