<?php
/**
 * Script para Atualizar Estrutura do Banco de Dados
 * Execute este arquivo para adicionar colunas que faltam
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Banco de Dados - E-Condo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #218838;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Atualizar Estrutura do Banco de Dados</h1>

<?php

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "<div class='info'>";
    echo "<h3>📊 Verificando estrutura do banco...</h3>";
    echo "</div>";
    
    $updates = [];
    $errors = [];
    
    // Verificar se coluna 'complement' existe na tabela 'houses'
    echo "<div class='info'>";
    echo "<h4>Verificando tabela 'houses'...</h4>";
    
    $result = $pdo->query("SHOW COLUMNS FROM houses LIKE 'complement'");
    
    if ($result->rowCount() == 0) {
        echo "<p>❌ Coluna 'complement' não existe. Adicionando...</p>";
        
        try {
            $pdo->exec("ALTER TABLE houses ADD COLUMN complement VARCHAR(100) DEFAULT NULL AFTER house_number");
            echo "<p class='success'>✅ Coluna 'complement' adicionada com sucesso!</p>";
            $updates[] = "Coluna 'complement' adicionada à tabela 'houses'";
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Erro ao adicionar coluna: " . $e->getMessage() . "</p>";
            $errors[] = "Erro ao adicionar coluna 'complement': " . $e->getMessage();
        }
    } else {
        echo "<p>✅ Coluna 'complement' já existe.</p>";
    }
    
    echo "</div>";
    
    // Verificar outras colunas que podem estar faltando
    echo "<div class='info'>";
    echo "<h4>Verificando outras estruturas...</h4>";
    
    // Verificar tabela residents
    $result = $pdo->query("SHOW TABLES LIKE 'residents'");
    if ($result->rowCount() > 0) {
        echo "<p>✅ Tabela 'residents' existe.</p>";
    } else {
        echo "<p class='warning'>⚠️ Tabela 'residents' não existe. Execute o schema.sql completo.</p>";
    }
    
    // Verificar tabela packages
    $result = $pdo->query("SHOW TABLES LIKE 'packages'");
    if ($result->rowCount() > 0) {
        echo "<p>✅ Tabela 'packages' existe.</p>";
    } else {
        echo "<p class='warning'>⚠️ Tabela 'packages' não existe. Execute o schema.sql completo.</p>";
    }
    
    echo "</div>";
    
    // Resumo
    if (empty($errors) && empty($updates)) {
        echo "<div class='success'>";
        echo "<h3>🎉 Banco de Dados Atualizado!</h3>";
        echo "<p>Todas as estruturas estão corretas. Nenhuma atualização necessária.</p>";
        echo "</div>";
    } elseif (empty($errors)) {
        echo "<div class='success'>";
        echo "<h3>🎉 Atualizações Aplicadas com Sucesso!</h3>";
        echo "<ul>";
        foreach ($updates as $update) {
            echo "<li>{$update}</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Erros Encontrados</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>{$error}</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // Verificar estrutura atual da tabela houses
    echo "<div class='info'>";
    echo "<h4>📋 Estrutura atual da tabela 'houses':</h4>";
    
    $result = $pdo->query("DESCRIBE houses");
    $columns = $result->fetchAll();
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Padrão</th>";
    echo "</tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='houses/list.php' class='btn'>📋 Ir para Casas</a>";
    echo "<a href='index.php' class='btn' style='background: #667eea;'>🏠 Ir para Dashboard</a>";
    echo "</div>";
    
    echo "<div class='warning' style='margin-top: 30px;'>";
    echo "<h4>⚠️ IMPORTANTE</h4>";
    echo "<p>Após executar as atualizações, você pode deletar este arquivo (<code>update_database.php</code>) por segurança.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Erro Fatal</h3>";
    echo "<p><strong>Mensagem:</strong> {$e->getMessage()}</p>";
    echo "<p><strong>Arquivo:</strong> {$e->getFile()}</p>";
    echo "<p><strong>Linha:</strong> {$e->getLine()}</p>";
    echo "</div>";
}

?>

    </div>
</body>
</html>
