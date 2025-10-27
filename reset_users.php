<?php
/**
 * Script para RESETAR usuários com senhas corretas
 * Execute este arquivo UMA VEZ para corrigir o problema de login
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
    <title>Reset de Usuários - E-Condo</title>
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
            margin-bottom: 30px;
        }
        .box {
            padding: 20px;
            margin: 20px 0;
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
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
        }
        .btn:hover {
            background: #218838;
        }
        code {
            background: #f4f4f4;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
        }
        .credentials {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        .credentials strong {
            color: #50fa7b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Reset de Usuários e Senhas</h1>

<?php

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "<div class='box info'>";
    echo "<h3>📊 Status Atual</h3>";
    
    // Verificar usuários existentes
    $stmt = $pdo->query("SELECT id, username, full_name, role, status FROM users");
    $existing_users = $stmt->fetchAll();
    
    if (count($existing_users) > 0) {
        echo "<p>Usuários encontrados: <strong>" . count($existing_users) . "</strong></p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Usuário</th><th>Nome</th><th>Perfil</th><th>Status</th></tr>";
        foreach ($existing_users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td><code>{$user['username']}</code></td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>⚠️ Nenhum usuário encontrado no banco.</p>";
    }
    
    echo "</div>";
    
    // DELETAR todos os usuários
    echo "<div class='box warning'>";
    echo "<h3>🗑️ Limpando usuários antigos...</h3>";
    $pdo->exec("DELETE FROM users");
    echo "<p>✅ Usuários antigos removidos.</p>";
    echo "</div>";
    
    // CRIAR NOVOS USUÁRIOS com senha correta
    echo "<div class='box info'>";
    echo "<h3>👥 Criando novos usuários...</h3>";
    
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<p><strong>Senha padrão:</strong> <code>{$password}</code></p>";
    echo "<p><strong>Hash gerado:</strong></p>";
    echo "<code style='word-break: break-all; display: block; margin: 10px 0;'>{$hash}</code>";
    
    // Verificar se o hash funciona
    if (password_verify($password, $hash)) {
        echo "<p style='color: #28a745;'>✅ Hash validado com sucesso!</p>";
    } else {
        echo "<p style='color: #dc3545;'>❌ Erro na validação do hash!</p>";
    }
    
    echo "</div>";
    
    // Inserir usuários
    echo "<div class='box'>";
    echo "<h3>➕ Inserindo usuários...</h3>";
    
    $users = [
        ['admin', 'Administrador do Sistema', 'admin@econdo.com', 'admin'],
        ['porteiro1', 'Porteiro Principal', 'porteiro@econdo.com', 'porteiro'],
        ['adm1', 'Administração Interna', 'administracao@econdo.com', 'administracao']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, full_name, email, role, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'ativo', NOW())
    ");
    
    $inserted = 0;
    foreach ($users as $user) {
        try {
            $stmt->execute([$user[0], $hash, $user[1], $user[2], $user[3]]);
            echo "<p>✅ Usuário <code>{$user[0]}</code> criado com sucesso!</p>";
            $inserted++;
        } catch (PDOException $e) {
            echo "<p style='color: #dc3545;'>❌ Erro ao criar {$user[0]}: {$e->getMessage()}</p>";
        }
    }
    
    echo "<p><strong>Total inserido: {$inserted} usuário(s)</strong></p>";
    echo "</div>";
    
    // Verificar usuários criados
    echo "<div class='box'>";
    echo "<h3>🔍 Verificando usuários criados...</h3>";
    
    $stmt = $pdo->query("SELECT id, username, full_name, email, role, status, LEFT(password, 20) as pwd_preview FROM users ORDER BY id");
    $new_users = $stmt->fetchAll();
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Usuário</th><th>Nome</th><th>Email</th><th>Perfil</th><th>Status</th><th>Hash (preview)</th></tr>";
    foreach ($new_users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td><code>{$user['username']}</code></td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['status']}</td>";
        echo "<td><code>{$user['pwd_preview']}...</code></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "</div>";
    
    // Testar login
    echo "<div class='box info'>";
    echo "<h3>🧪 Testando autenticação...</h3>";
    
    $test_username = 'admin';
    $test_password = 'admin123';
    
    $stmt = $pdo->prepare("SELECT password FROM users WHERE username = ? AND status = 'ativo'");
    $stmt->execute([$test_username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p>✅ Usuário <code>{$test_username}</code> encontrado no banco.</p>";
        
        if (password_verify($test_password, $user['password'])) {
            echo "<p style='color: #28a745; font-weight: bold; font-size: 18px;'>✅ AUTENTICAÇÃO FUNCIONANDO! Senha verificada com sucesso!</p>";
        } else {
            echo "<p style='color: #dc3545; font-weight: bold;'>❌ Erro: Senha não confere!</p>";
        }
    } else {
        echo "<p style='color: #dc3545;'>❌ Usuário não encontrado!</p>";
    }
    
    echo "</div>";
    
    // SUCESSO!
    echo "<div class='box success'>";
    echo "<h2>🎉 Reset Concluído com Sucesso!</h2>";
    echo "<p><strong>Os usuários foram recriados com senhas corretas.</strong></p>";
    
    echo "<div class='credentials'>";
    echo "<h3 style='color: #50fa7b; margin-top: 0;'>📝 Credenciais de Login:</h3>";
    echo "<p><strong>URL:</strong> http://localhost/e-condo</p>";
    echo "<br>";
    echo "<p><strong>Administrador:</strong></p>";
    echo "<p>Usuário: <strong>admin</strong></p>";
    echo "<p>Senha: <strong>admin123</strong></p>";
    echo "<br>";
    echo "<p><strong>Porteiro:</strong></p>";
    echo "<p>Usuário: <strong>porteiro1</strong></p>";
    echo "<p>Senha: <strong>admin123</strong></p>";
    echo "<br>";
    echo "<p><strong>Administração:</strong></p>";
    echo "<p>Usuário: <strong>adm1</strong></p>";
    echo "<p>Senha: <strong>admin123</strong></p>";
    echo "</div>";
    
    echo "<p style='text-align: center; margin-top: 30px;'>";
    echo "<a href='login.php' class='btn'>🚀 IR PARA O LOGIN</a>";
    echo "</p>";
    
    echo "</div>";
    
    echo "<div class='box warning'>";
    echo "<h3>⚠️ IMPORTANTE - Segurança</h3>";
    echo "<ol>";
    echo "<li>Faça login no sistema</li>";
    echo "<li>Altere TODAS as senhas padrão</li>";
    echo "<li><strong>DELETE este arquivo (reset_users.php) após usar!</strong></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='box error'>";
    echo "<h2>❌ Erro</h2>";
    echo "<p><strong>Mensagem:</strong> {$e->getMessage()}</p>";
    echo "<p><strong>Arquivo:</strong> {$e->getFile()}</p>";
    echo "<p><strong>Linha:</strong> {$e->getLine()}</p>";
    echo "</div>";
}

?>

    </div>
</body>
</html>
