<?php
/**
 * Script para gerar hash de senha
 * Use este script para gerar hashes de senha corretos
 */

// Senha padrão
$password = 'admin123';

// Gerar hash
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "=================================\n";
echo "GERADOR DE HASH DE SENHA\n";
echo "=================================\n\n";
echo "Senha: {$password}\n";
echo "Hash: {$hash}\n\n";

// Testar verificação
if (password_verify($password, $hash)) {
    echo "✅ Verificação OK!\n\n";
} else {
    echo "❌ Erro na verificação!\n\n";
}

echo "=================================\n";
echo "SQL para atualizar usuários:\n";
echo "=================================\n\n";

echo "UPDATE users SET password = '{$hash}' WHERE username = 'admin';\n";
echo "UPDATE users SET password = '{$hash}' WHERE username = 'porteiro1';\n";
echo "UPDATE users SET password = '{$hash}' WHERE username = 'adm1';\n\n";

echo "=================================\n";
echo "Ou execute este script diretamente:\n";
echo "=================================\n";
echo "php generate_password.php\n\n";
?>
