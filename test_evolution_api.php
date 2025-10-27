<?php
/**
 * Script para testar endpoints da Evolution API
 */

$apiUrl = 'https://api.sisunico.shop';
$apiKey = 'F11E1A8F5BC7-466F-AEC1-DFF592D81CB0';
$instanceName = 'e-condomínio';

echo "<h2>🔍 Teste da Evolution API</h2>";
echo "<p><strong>URL:</strong> $apiUrl</p>";
echo "<p><strong>Instância:</strong> $instanceName</p>";
echo "<hr>";

// Teste 1: Verificar se a API está acessível
echo "<h3>1. Testando conexão com a API base</h3>";
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<pre>";
if ($error) {
    echo "❌ ERRO: $error\n";
} else {
    echo "✅ Conectado! HTTP Code: $httpCode\n";
    echo "Resposta: " . substr($response, 0, 300) . "\n";
}
echo "</pre>";

// Teste 2: Verificar status da instância
echo "<h3>2. Verificando status da instância</h3>";
$url = "$apiUrl/instance/connectionState/$instanceName";
echo "<p>Endpoint: <code>$url</code></p>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $apiKey"]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<pre>";
if ($error) {
    echo "❌ ERRO: $error\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Resposta: $response\n\n";
    
    if ($httpCode == 404) {
        echo "⚠️ PROBLEMA: Instância '$instanceName' não encontrada!\n";
        echo "\nPossíveis causas:\n";
        echo "1. Nome da instância está incorreto\n";
        echo "2. Instância não foi criada na Evolution API\n";
        echo "3. Instância foi deletada\n";
    } elseif ($httpCode == 200) {
        $data = json_decode($response, true);
        if (isset($data['state'])) {
            echo "✅ Status da instância: " . $data['state'] . "\n";
        }
    }
}
echo "</pre>";

// Teste 3: Listar todas as instâncias
echo "<h3>3. Listando todas as instâncias disponíveis</h3>";
$url = "$apiUrl/instance/fetchInstances";
echo "<p>Endpoint: <code>$url</code></p>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $apiKey"]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<pre>";
echo "HTTP Code: $httpCode\n";
echo "Resposta: $response\n\n";

if ($httpCode == 200) {
    $instances = json_decode($response, true);
    if (is_array($instances) && count($instances) > 0) {
        echo "✅ Instâncias encontradas:\n";
        foreach ($instances as $inst) {
            $name = is_array($inst) ? ($inst['instance']['instanceName'] ?? $inst['instanceName'] ?? 'N/A') : 'N/A';
            echo "  - $name\n";
        }
    } else {
        echo "⚠️ Nenhuma instância encontrada\n";
    }
}
echo "</pre>";

// Teste 4: Testar endpoint de envio de mensagem
echo "<h3>4. Testando endpoint de envio (sem enviar)</h3>";
$url = "$apiUrl/message/sendText/$instanceName";
echo "<p>Endpoint que o sistema está usando: <code>$url</code></p>";

echo "<hr>";
echo "<h3>📝 Recomendações:</h3>";
echo "<ol>";
echo "<li>Verifique o nome correto da instância na lista acima</li>";
echo "<li>Se o nome estiver diferente, atualize no banco de dados</li>";
echo "<li>Certifique-se de que a instância está conectada (state: 'open')</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='admin/settings.php'>← Voltar para Configurações</a></p>";
