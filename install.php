<?php
/**
 * Script de Instalação Automática - E-Condo Packages
 * Execute este arquivo APENAS UMA VEZ após copiar os arquivos
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - E-Condo Packages</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 2rem; margin-bottom: 10px; }
        .content { padding: 30px; }
        .step {
            background: #f8f9fa;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 10px 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover { background: #5568d3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }
        .progress {
            background: #e9ecef;
            border-radius: 10px;
            height: 30px;
            margin: 20px 0;
            overflow: hidden;
        }
        .progress-bar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Instalação E-Condo Packages</h1>
            <p>Configuração automática do sistema</p>
        </div>
        <div class="content">

<?php

// Configurações do banco (lendo do config.php)
require_once __DIR__ . '/config/config.php';

$db_config = [
    'host' => DB_HOST,
    'name' => DB_NAME,
    'user' => DB_USER,
    'pass' => DB_PASS
];

$steps_completed = 0;
$total_steps = 5;
$errors = [];
$warnings = [];

// PASSO 1: Testar conexão com MySQL
echo "<div class='step'>";
echo "<h3>📡 Passo 1: Testando conexão com MySQL</h3>";

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']}", 
        $db_config['user'], 
        $db_config['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✅ Conexão com MySQL estabelecida com sucesso!</p>";
    $steps_completed++;
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro ao conectar ao MySQL: " . $e->getMessage() . "</p>";
    echo "<p class='warning'>⚠️ Verifique se o MySQL está rodando no XAMPP!</p>";
    $errors[] = "Conexão MySQL falhou";
}

echo "</div>";

// PASSO 2: Criar/Verificar banco de dados
if (empty($errors)) {
    echo "<div class='step'>";
    echo "<h3>🗄️ Passo 2: Criando banco de dados</h3>";
    
    try {
        // Verificar se banco existe
        $result = $pdo->query("SHOW DATABASES LIKE '{$db_config['name']}'");
        
        if ($result->rowCount() > 0) {
            echo "<p class='warning'>⚠️ Banco de dados '{$db_config['name']}' já existe.</p>";
            
            // Perguntar se quer recriar
            if (isset($_GET['recreate']) && $_GET['recreate'] == '1') {
                $pdo->exec("DROP DATABASE {$db_config['name']}");
                echo "<p class='info'>🗑️ Banco antigo removido.</p>";
                
                $pdo->exec("CREATE DATABASE {$db_config['name']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo "<p class='success'>✅ Novo banco criado!</p>";
            } else {
                echo "<p class='info'>ℹ️ Usando banco existente. <a href='?recreate=1' class='btn'>Recriar Banco</a></p>";
            }
        } else {
            $pdo->exec("CREATE DATABASE {$db_config['name']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<p class='success'>✅ Banco de dados criado com sucesso!</p>";
        }
        
        $pdo->exec("USE {$db_config['name']}");
        $steps_completed++;
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao criar banco: " . $e->getMessage() . "</p>";
        $errors[] = "Criação do banco falhou";
    }
    
    echo "</div>";
}

// PASSO 3: Criar tabelas
if (empty($errors)) {
    echo "<div class='step'>";
    echo "<h3>📋 Passo 3: Criando tabelas</h3>";
    
    try {
        // Ler arquivo SQL
        $sql_file = __DIR__ . '/database/schema.sql';
        
        if (!file_exists($sql_file)) {
            throw new Exception("Arquivo schema.sql não encontrado em: {$sql_file}");
        }
        
        $sql = file_get_contents($sql_file);
        
        // Remover comentários e dividir por comandos
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Executar cada comando separadamente
        $commands = explode(';', $sql);
        $executed = 0;
        
        foreach ($commands as $command) {
            $command = trim($command);
            if (!empty($command)) {
                try {
                    $pdo->exec($command);
                    $executed++;
                } catch (PDOException $e) {
                    // Ignorar erros de "já existe"
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }
        }
        
        echo "<p class='success'>✅ {$executed} comandos SQL executados com sucesso!</p>";
        
        // Verificar tabelas criadas
        $result = $pdo->query("SHOW TABLES");
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p class='info'>📊 Tabelas criadas: " . count($tables) . "</p>";
        echo "<div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 10px 0;'>";
        foreach ($tables as $table) {
            echo "<div style='background: white; padding: 8px; border-radius: 4px; border: 1px solid #dee2e6;'>✓ {$table}</div>";
        }
        echo "</div>";
        
        $steps_completed++;
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao criar tabelas: " . $e->getMessage() . "</p>";
        $errors[] = "Criação de tabelas falhou";
    }
    
    echo "</div>";
}

// PASSO 4: Criar usuários com senhas corretas
if (empty($errors)) {
    echo "<div class='step'>";
    echo "<h3>👥 Passo 4: Criando usuários do sistema</h3>";
    
    try {
        // Limpar usuários existentes
        $pdo->exec("DELETE FROM users");
        
        // Gerar hash correto para senha 'admin123'
        $password = 'admin123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        echo "<p><strong>Senha padrão:</strong> <code>{$password}</code></p>";
        echo "<p><strong>Hash gerado:</strong> <code style='font-size: 10px;'>{$hash}</code></p>";
        
        // Inserir usuários
        $users = [
            ['admin', 'Administrador do Sistema', 'admin@econdo.com', 'admin'],
            ['porteiro1', 'Porteiro Principal', 'porteiro@econdo.com', 'porteiro'],
            ['adm1', 'Administração Interna', 'administracao@econdo.com', 'administracao']
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, full_name, email, role, status) 
            VALUES (?, ?, ?, ?, ?, 'ativo')
        ");
        
        foreach ($users as $user) {
            $stmt->execute([$user[0], $hash, $user[1], $user[2], $user[3]]);
        }
        
        echo "<p class='success'>✅ " . count($users) . " usuários criados com sucesso!</p>";
        
        // Mostrar usuários
        echo "<table>";
        echo "<tr><th>Usuário</th><th>Nome</th><th>Perfil</th><th>Senha</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td><strong>{$user[0]}</strong></td>";
            echo "<td>{$user[1]}</td>";
            echo "<td>{$user[3]}</td>";
            echo "<td><code>{$password}</code></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        $steps_completed++;
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao criar usuários: " . $e->getMessage() . "</p>";
        $errors[] = "Criação de usuários falhou";
    }
    
    echo "</div>";
}

// PASSO 5: Configurações finais
if (empty($errors)) {
    echo "<div class='step'>";
    echo "<h3>⚙️ Passo 5: Configurações finais</h3>";
    
    try {
        // Verificar se configurações existem
        $result = $pdo->query("SELECT COUNT(*) FROM system_settings");
        $count = $result->fetchColumn();
        
        echo "<p class='success'>✅ {$count} configurações do sistema carregadas!</p>";
        
        // Inserir dados de exemplo
        $result = $pdo->query("SELECT COUNT(*) FROM villages");
        $villages_count = $result->fetchColumn();
        
        if ($villages_count == 0) {
            echo "<p class='info'>ℹ️ Nenhuma village cadastrada. Você pode cadastrar após o login.</p>";
        } else {
            echo "<p class='success'>✅ {$villages_count} village(s) já cadastrada(s)!</p>";
        }
        
        $steps_completed++;
        
    } catch (PDOException $e) {
        echo "<p class='warning'>⚠️ Aviso: " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
}

// Barra de progresso
$progress = ($steps_completed / $total_steps) * 100;

echo "<div class='progress'>";
echo "<div class='progress-bar' style='width: {$progress}%'>";
echo round($progress) . "%";
echo "</div>";
echo "</div>";

// Resultado final
if (empty($errors)) {
    echo "<div class='step success'>";
    echo "<h2>🎉 Instalação Concluída com Sucesso!</h2>";
    echo "<p><strong>O sistema está pronto para uso!</strong></p>";
    echo "<br>";
    echo "<h3>📝 Credenciais de Acesso:</h3>";
    echo "<div class='code'>";
    echo "URL: http://localhost/e-condo<br>";
    echo "Usuário: admin<br>";
    echo "Senha: admin123<br>";
    echo "</div>";
    echo "<br>";
    echo "<h3>🎯 Próximos Passos:</h3>";
    echo "<ol>";
    echo "<li>Faça login no sistema</li>";
    echo "<li>Altere a senha padrão</li>";
    echo "<li>Configure o WhatsApp API (opcional)</li>";
    echo "<li>Cadastre villages e casas</li>";
    echo "<li>Cadastre condôminos</li>";
    echo "<li>Comece a registrar encomendas!</li>";
    echo "</ol>";
    echo "<br>";
    echo "<p style='text-align: center;'>";
    echo "<a href='login.php' class='btn btn-success' style='font-size: 18px; padding: 15px 40px;'>🚀 Acessar Sistema</a>";
    echo "</p>";
    echo "</div>";
    
    echo "<div class='step warning'>";
    echo "<h3>⚠️ IMPORTANTE - Segurança</h3>";
    echo "<p><strong>Após fazer login pela primeira vez:</strong></p>";
    echo "<ol>";
    echo "<li>Altere TODAS as senhas padrão</li>";
    echo "<li>Delete ou renomeie este arquivo (install.php)</li>";
    echo "<li>Configure permissões adequadas</li>";
    echo "</ol>";
    echo "</div>";
    
} else {
    echo "<div class='step error'>";
    echo "<h2>❌ Instalação Falhou</h2>";
    echo "<p><strong>Erros encontrados:</strong></p>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>{$error}</li>";
    }
    echo "</ul>";
    echo "<br>";
    echo "<h3>🔧 Soluções:</h3>";
    echo "<ol>";
    echo "<li>Verifique se o MySQL está rodando no XAMPP</li>";
    echo "<li>Verifique as credenciais do banco em config/config.php</li>";
    echo "<li>Certifique-se que o arquivo database/schema.sql existe</li>";
    echo "<li><a href='?' class='btn'>Tentar Novamente</a></li>";
    echo "</ol>";
    echo "</div>";
}

?>

        </div>
    </div>
</body>
</html>
