<?php
/**
 * Script para corrigir senhas dos usuários padrão
 * Execute este arquivo uma vez para corrigir as senhas
 */

require_once 'config/config.php';

echo "<h2>🔧 Corrigindo Senhas dos Usuários</h2>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Senha padrão
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<p><strong>Senha padrão:</strong> {$password}</p>";
    echo "<p><strong>Hash gerado:</strong> {$hash}</p>";
    
    // Atualizar todos os usuários
    $sql = "UPDATE users SET password = :password WHERE username IN ('admin', 'porteiro1', 'adm1')";
    $stmt = $db->prepare($sql);
    $stmt->execute(['password' => $hash]);
    
    $affected = $stmt->rowCount();
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724; margin: 0;'>✅ Senhas Atualizadas com Sucesso!</h3>";
    echo "<p style='margin: 10px 0 0 0;'>{$affected} usuário(s) atualizado(s)</p>";
    echo "</div>";
    
    // Verificar usuários
    $sql = "SELECT id, username, full_name, role, status FROM users ORDER BY id";
    $stmt = $db->query($sql);
    $users = $stmt->fetchAll();
    
    echo "<h3>👥 Usuários no Sistema:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>ID</th><th>Usuário</th><th>Nome</th><th>Perfil</th><th>Status</th>";
    echo "</tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['status']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #856404; margin: 0;'>📝 Credenciais de Login:</h3>";
    echo "<ul>";
    echo "<li><strong>Usuário:</strong> admin | <strong>Senha:</strong> admin123</li>";
    echo "<li><strong>Usuário:</strong> porteiro1 | <strong>Senha:</strong> admin123</li>";
    echo "<li><strong>Usuário:</strong> adm1 | <strong>Senha:</strong> admin123</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #0c5460; margin: 0;'>🎯 Próximos Passos:</h3>";
    echo "<ol>";
    echo "<li>Acesse: <a href='login.php'>login.php</a></li>";
    echo "<li>Use: <strong>admin</strong> / <strong>admin123</strong></li>";
    echo "<li>Após login, altere a senha em: Administração → Usuários</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p style='text-align: center; margin-top: 30px;'>";
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir para Login</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24; margin: 0;'>❌ Erro:</h3>";
    echo "<p>{$e->getMessage()}</p>";
    echo "</div>";
    
    echo "<h4>Possíveis causas:</h4>";
    echo "<ul>";
    echo "<li>Banco de dados não foi criado</li>";
    echo "<li>Tabela 'users' não existe</li>";
    echo "<li>Credenciais do banco incorretas em config/config.php</li>";
    echo "</ul>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h2 {
        color: #333;
        border-bottom: 3px solid #007bff;
        padding-bottom: 10px;
    }
</style>
