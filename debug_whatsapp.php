<?php
/**
 * Script de debug para WhatsApp Service
 */

require_once 'config/config.php';

echo "<h2>🔍 Debug - Configurações WhatsApp</h2>";

// 1. Verificar configurações no banco
echo "<h3>1. Configurações no Banco de Dados:</h3>";
$settingModel = new SystemSetting();
$config = $settingModel->getWhatsAppConfig();

echo "<pre>";
echo "API Key: " . ($config['api_key'] ?: '(vazio)') . "\n";
echo "Instance Name: " . ($config['instance_name'] ?: '(vazio)') . "\n";
echo "Enabled: " . ($config['enabled'] ? 'Sim' : 'Não') . "\n";
echo "URL da API: " . $settingModel->getEvolutionApiUrl() . "\n";
echo "</pre>";

// 2. Verificar WhatsAppService
echo "<h3>2. WhatsApp Service:</h3>";
$whatsappService = new WhatsAppService();

echo "<pre>";
echo "Serviço habilitado: " . ($whatsappService->isEnabled() ? 'Sim' : 'Não') . "\n";
echo "</pre>";

// 3. Verificar constantes do config.php
echo "<h3>3. Constantes do config.php:</h3>";
echo "<pre>";
echo "EVOLUTION_API_URL: " . (defined('EVOLUTION_API_URL') ? EVOLUTION_API_URL : '(não definida)') . "\n";
echo "</pre>";

// 4. Testar conexão com a API
echo "<h3>4. Teste de Conexão:</h3>";
$apiUrl = $settingModel->getEvolutionApiUrl();
echo "<pre>";
echo "Tentando conectar em: $apiUrl\n\n";

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ ERRO: $error\n";
} else {
    echo "✅ Conexão estabelecida!\n";
    echo "HTTP Code: $httpCode\n";
    echo "Resposta: " . substr($response, 0, 200) . "...\n";
}
echo "</pre>";

echo "<hr>";
echo "<p><a href='admin/settings.php'>← Voltar para Configurações</a></p>";
