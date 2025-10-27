<?php
/**
 * Teste de Conexão com MySQL
 * Use este script para verificar se as credenciais do banco estão corretas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Conexão - E-Condo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #5568d3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>🔍 Teste de Conexão MySQL</h1>

    <?php
    // Tentar carregar config.php
    $config_file = __DIR__ . '/config/config.php';
    
    if (!file_exists($config_file)) {
        echo "<div class='box error'>";
        echo "<h2>❌ Arquivo config.php não encontrado!</h2>";
        echo "<p>Caminho esperado: <code>{$config_file}</code></p>";
        echo "</div>";
        exit;
    }
    
    require_once $config_file;
    
    echo "<div class='box info'>";
    echo "<h2>📋 Configurações Carregadas</h2>";
    echo "<table>";
    echo "<tr><th>Configuração</th><th>Valor</th></tr>";
    echo "<tr><td>DB_HOST</td><td><code>" . DB_HOST . "</code></td></tr>";
    echo "<tr><td>DB_NAME</td><td><code>" . DB_NAME . "</code></td></tr>";
    echo "<tr><td>DB_USER</td><td><code>" . DB_USER . "</code></td></tr>";
    echo "<tr><td>DB_PASS</td><td><code>" . (DB_PASS ? str_repeat('*', strlen(DB_PASS)) : '(vazio)') . "</code></td></tr>";
    echo "<tr><td>DB_CHARSET</td><td><code>" . DB_CHARSET . "</code></td></tr>";
    echo "</table>";
    echo "</div>";
    
    // Teste 1: Conexão sem banco específico
    echo "<div class='box'>";
    echo "<h2>🧪 Teste 1: Conexão com MySQL (sem banco)</h2>";
    
    try {
        $dsn = "mysql:host=" . DB_HOST;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p class='success' style='padding: 15px; border-radius: 5px;'>";
        echo "✅ <strong>Conexão com MySQL estabelecida com sucesso!</strong>";
        echo "</p>";
        
        // Verificar versão do MySQL
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        echo "<p>📊 Versão do MySQL: <code>{$version}</code></p>";
        
    } catch (PDOException $e) {
        echo "<p class='error' style='padding: 15px; border-radius: 5px;'>";
        echo "❌ <strong>Erro ao conectar:</strong><br>";
        echo $e->getMessage();
        echo "</p>";
        
        echo "<div class='warning' style='padding: 15px; border-radius: 5px; margin-top: 15px;'>";
        echo "<h3>🔧 Possíveis Soluções:</h3>";
        echo "<ol>";
        echo "<li>Verifique se o MySQL está rodando no XAMPP</li>";
        echo "<li>Verifique o usuário: <code>" . DB_USER . "</code></li>";
        echo "<li>Verifique a senha em <code>config/config.php</code></li>";
        echo "<li>Se a senha tem caracteres especiais, tente sem aspas</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "</div>";
        exit;
    }
    echo "</div>";
    
    // Teste 2: Verificar se banco existe
    echo "<div class='box'>";
    echo "<h2>🧪 Teste 2: Verificar banco de dados</h2>";
    
    try {
        $result = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        
        if ($result->rowCount() > 0) {
            echo "<p class='success' style='padding: 15px; border-radius: 5px;'>";
            echo "✅ Banco de dados <code>" . DB_NAME . "</code> existe!";
            echo "</p>";
            
            // Conectar ao banco específico
            $pdo->exec("USE " . DB_NAME);
            
            // Listar tabelas
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) > 0) {
                echo "<p>📊 Tabelas encontradas: <strong>" . count($tables) . "</strong></p>";
                echo "<div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;'>";
                foreach ($tables as $table) {
                    echo "<div style='background: #f8f9fa; padding: 8px; border-radius: 4px;'>✓ {$table}</div>";
                }
                echo "</div>";
                
                // Verificar usuários
                try {
                    $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                    echo "<p style='margin-top: 15px;'>👥 Usuários cadastrados: <strong>{$users}</strong></p>";
                    
                    if ($users > 0) {
                        $user_list = $pdo->query("SELECT username, full_name, role, status FROM users")->fetchAll();
                        echo "<table>";
                        echo "<tr><th>Usuário</th><th>Nome</th><th>Perfil</th><th>Status</th></tr>";
                        foreach ($user_list as $user) {
                            echo "<tr>";
                            echo "<td><code>{$user['username']}</code></td>";
                            echo "<td>{$user['full_name']}</td>";
                            echo "<td>{$user['role']}</td>";
                            echo "<td>{$user['status']}</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='warning' style='padding: 10px; border-radius: 5px; margin-top: 10px;'>";
                    echo "⚠️ Tabela 'users' não encontrada ou vazia";
                    echo "</p>";
                }
                
            } else {
                echo "<p class='warning' style='padding: 15px; border-radius: 5px;'>";
                echo "⚠️ Banco existe mas está vazio (sem tabelas)";
                echo "</p>";
            }
            
        } else {
            echo "<p class='warning' style='padding: 15px; border-radius: 5px;'>";
            echo "⚠️ Banco de dados <code>" . DB_NAME . "</code> NÃO existe!";
            echo "</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p class='error' style='padding: 15px; border-radius: 5px;'>";
        echo "❌ Erro: " . $e->getMessage();
        echo "</p>";
    }
    
    echo "</div>";
    
    // Teste 3: Testar classe Database
    echo "<div class='box'>";
    echo "<h2>🧪 Teste 3: Testar classe Database.php</h2>";
    
    try {
        require_once __DIR__ . '/app/core/Database.php';
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        echo "<p class='success' style='padding: 15px; border-radius: 5px;'>";
        echo "✅ Classe Database funcionando corretamente!";
        echo "</p>";
        
        // Testar query
        $result = $conn->query("SELECT 1 as test")->fetch();
        if ($result['test'] == 1) {
            echo "<p>✅ Queries funcionando normalmente</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error' style='padding: 15px; border-radius: 5px;'>";
        echo "❌ Erro na classe Database: " . $e->getMessage();
        echo "</p>";
    }
    
    echo "</div>";
    
    // Resumo e próximos passos
    echo "<div class='box success'>";
    echo "<h2>🎯 Próximos Passos</h2>";
    
    if (count($tables ?? []) > 0 && ($users ?? 0) > 0) {
        echo "<p><strong>✅ Tudo está configurado corretamente!</strong></p>";
        echo "<p>Você pode fazer login no sistema:</p>";
        echo "<ul>";
        echo "<li>Usuário: <code>admin</code></li>";
        echo "<li>Senha: <code>admin123</code></li>";
        echo "</ul>";
        echo "<p style='text-align: center; margin-top: 20px;'>";
        echo "<a href='login.php' class='btn' style='font-size: 18px; padding: 15px 30px;'>🚀 Ir para Login</a>";
        echo "</p>";
    } else {
        echo "<p><strong>⚠️ Sistema precisa ser instalado</strong></p>";
        echo "<p>Execute o instalador automático:</p>";
        echo "<p style='text-align: center; margin-top: 20px;'>";
        echo "<a href='install.php' class='btn' style='font-size: 18px; padding: 15px 30px;'>🔧 Executar Instalador</a>";
        echo "</p>";
    }
    
    echo "</div>";
    ?>

</body>
</html>
