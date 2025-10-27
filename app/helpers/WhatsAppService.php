<?php
/**
 * WhatsApp Service - Integração com Evolution API
 * 
 * Serviço para envio de mensagens via Evolution API
 * Documentação: https://doc.evolution-api.com/
 */

class WhatsAppService {
    private $apiKey;
    private $instanceName;
    private $enabled;
    private $apiUrl;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->loadConfig();
    }
    
    /**
     * Carregar configurações do banco de dados
     */
    private function loadConfig() {
        $settingModel = new SystemSetting();
        $config = $settingModel->getWhatsAppConfig();
        
        $this->apiKey = $config['api_key'] ?? $config['token'] ?? null;
        $this->instanceName = $config['instance_name'] ?? $config['phone_id'] ?? null;
        $this->enabled = $config['enabled'];
        
        // Carregar URL do banco de dados (prioridade) ou usar padrão do config.php
        $this->apiUrl = $settingModel->getEvolutionApiUrl();
    }
    
    /**
     * Verificar se WhatsApp está habilitado
     */
    public function isEnabled() {
        return $this->enabled && !empty($this->apiKey) && !empty($this->instanceName);
    }
    
    /**
     * Enviar mensagem de texto via Evolution API
     */
    public function sendMessage($to, $message) {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'WhatsApp não está habilitado ou configurado'
            ];
        }
        
        // Formatar número de telefone (apenas números, com código do país)
        $to = preg_replace('/[^0-9]/', '', $to);
        
        // Se não começar com 55 (Brasil), adicionar
        if (substr($to, 0, 2) !== '55') {
            $to = '55' . $to;
        }
        
        // Evolution API endpoint para enviar mensagem de texto
        $url = "{$this->apiUrl}/message/sendText/{$this->instanceName}";
        
        $data = [
            'number' => $to,
            'text' => $message
        ];
        
        $headers = [
            'apikey: ' . $this->apiKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Evolution API pode usar SSL auto-assinado
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'response' => $responseData,
                'http_code' => $httpCode
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseData['message'] ?? $responseData['error'] ?? 'Erro desconhecido',
                'response' => $responseData,
                'http_code' => $httpCode
            ];
        }
    }
    
    /**
     * Enviar imagem (QR Code) via Evolution API
     */
    public function sendImage($to, $imageUrl, $caption = '') {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'WhatsApp não está habilitado ou configurado'
            ];
        }
        
        // Formatar número de telefone
        $to = preg_replace('/[^0-9]/', '', $to);
        if (substr($to, 0, 2) !== '55') {
            $to = '55' . $to;
        }
        
        // Evolution API endpoint para enviar mídia
        $url = "{$this->apiUrl}/message/sendMedia/{$this->instanceName}";
        
        $data = [
            'number' => $to,
            'mediatype' => 'image',
            'media' => $imageUrl
        ];
        
        if (!empty($caption)) {
            $data['caption'] = $caption;
        }
        
        $headers = [
            'apikey: ' . $this->apiKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'response' => $responseData,
                'http_code' => $httpCode
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseData['message'] ?? $responseData['error'] ?? 'Erro desconhecido',
                'response' => $responseData,
                'http_code' => $httpCode
            ];
        }
    }
    
    /**
     * Enviar notificação de recebimento de encomenda com QR Code
     */
    public function sendPackageReceivedNotification($packageId, $residentName, $phone, $trackingCode) {
        // Mensagem simplificada
        $message = "📦 *Encomenda Recebida*\n\n";
        $message .= "Olá *{$residentName}*!\n\n";
        $message .= "Sua encomenda chegou na portaria.\n\n";
        $message .= "*Código:* ```{$trackingCode}```\n\n";
        
        // Link para QR Code
        $qrViewUrl = SITE_URL . "/api/qrcode.php?code=" . urlencode($trackingCode);
        $message .= "🔗 Ver QR Code: {$qrViewUrl}\n\n";
        
        $message .= "Apresente o código ou QR Code para retirar.";
        
        $result = $this->sendMessage($phone, $message);
        
        // Registrar notificação no banco
        $this->logNotification($packageId, $residentName, $phone, $message, 'recebimento', $result);
        
        // Tentar enviar QR Code se a URL for pública
        if ($result['success']) {
            // Verificar se SITE_URL é uma URL pública (não localhost)
            $isPublicUrl = !preg_match('/localhost|127\.0\.0\.1|192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\./i', SITE_URL);
            
            if ($isPublicUrl) {
                // URL pública - tentar enviar QR Code como imagem
                $qrCodeUrl = SITE_URL . "/api/generate_qrcode.php?code=" . urlencode($trackingCode);
                $caption = "📱 QR Code para retirada\nCódigo: {$trackingCode}";
                
                // Aguardar 2 segundos antes de enviar a imagem
                sleep(2);
                
                $qrResult = $this->sendImage($phone, $qrCodeUrl, $caption);
                
                // Registrar envio do QR Code
                $qrMessage = "QR Code enviado para retirada";
                $this->logNotification($packageId, $residentName, $phone, $qrMessage, 'qrcode', $qrResult);
            }
            // Se for URL local, não registra notificação de erro
            // O link do QR Code já foi enviado na mensagem de texto
        }
        
        return $result;
    }
    
    /**
     * Enviar notificação de retirada de encomenda
     */
    public function sendPackagePickedUpNotification($packageId, $residentName, $phone, $trackingCode) {
        $message = "Olá {$residentName}, sua encomenda com código *{$trackingCode}* foi retirada com sucesso.\n\n";
        $message .= "Obrigado!";
        
        $result = $this->sendMessage($phone, $message);
        
        // Registrar notificação no banco
        $this->logNotification($packageId, $residentName, $phone, $message, 'retirada', $result);
        
        return $result;
    }
    
    /**
     * Registrar notificação no banco de dados
     */
    private function logNotification($packageId, $residentName, $phone, $message, $type, $result) {
        // Buscar resident_id pelo packageId
        $sql = "SELECT resident_id FROM packages WHERE id = :package_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['package_id' => $packageId]);
        $package = $stmt->fetch();
        
        if (!$package) {
            return false;
        }
        
        $data = [
            'package_id' => $packageId,
            'resident_id' => $package['resident_id'],
            'phone' => $phone,
            'message' => $message,
            'notification_type' => $type,
            'status' => $result['success'] ? 'enviada' : 'erro',
            'response_data' => json_encode($result['response'] ?? null),
            'error_message' => $result['error'] ?? null,
            'sent_at' => $result['success'] ? date('Y-m-d H:i:s') : null
        ];
        
        $sql = "INSERT INTO whatsapp_notifications 
                (package_id, resident_id, phone, message, notification_type, status, response_data, error_message, sent_at) 
                VALUES 
                (:package_id, :resident_id, :phone, :message, :notification_type, :status, :response_data, :error_message, :sent_at)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Buscar histórico de notificações de uma encomenda
     */
    public function getPackageNotifications($packageId) {
        $sql = "SELECT * FROM whatsapp_notifications WHERE package_id = :package_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['package_id' => $packageId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Reenviar notificação falhada
     */
    public function resendNotification($notificationId) {
        $sql = "SELECT * FROM whatsapp_notifications WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $notificationId]);
        $notification = $stmt->fetch();
        
        if (!$notification) {
            return ['success' => false, 'error' => 'Notificação não encontrada'];
        }
        
        $result = $this->sendMessage($notification['phone'], $notification['message']);
        
        // Atualizar status da notificação
        $updateSql = "UPDATE whatsapp_notifications 
                      SET status = :status, 
                          response_data = :response_data, 
                          error_message = :error_message,
                          sent_at = :sent_at
                      WHERE id = :id";
        
        $updateStmt = $this->db->prepare($updateSql);
        $updateStmt->execute([
            'id' => $notificationId,
            'status' => $result['success'] ? 'enviada' : 'erro',
            'response_data' => json_encode($result['response'] ?? null),
            'error_message' => $result['error'] ?? null,
            'sent_at' => $result['success'] ? date('Y-m-d H:i:s') : null
        ]);
        
        return $result;
    }
    
    /**
     * Testar configuração do WhatsApp
     */
    public function testConnection($testPhone) {
        $message = "Teste de conexão do E-Condo Packages.\n\nSe você recebeu esta mensagem, a integração está funcionando corretamente!";
        return $this->sendMessage($testPhone, $message);
    }
    
    /**
     * Verificar status da instância na Evolution API
     */
    public function getInstanceStatus() {
        if (empty($this->apiKey) || empty($this->instanceName)) {
            return [
                'success' => false,
                'error' => 'API Key ou Instance Name não configurados'
            ];
        }
        
        $url = "{$this->apiUrl}/instance/connectionState/{$this->instanceName}";
        
        $headers = [
            'apikey: ' . $this->apiKey
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'response' => $responseData,
                'http_code' => $httpCode,
                'connected' => ($responseData['state'] ?? '') === 'open'
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseData['message'] ?? $responseData['error'] ?? 'Erro desconhecido',
                'response' => $responseData,
                'http_code' => $httpCode
            ];
        }
    }
    
    /**
     * Obter QR Code para conectar instância
     */
    public function getQRCode() {
        if (empty($this->apiKey) || empty($this->instanceName)) {
            return [
                'success' => false,
                'error' => 'API Key ou Instance Name não configurados'
            ];
        }
        
        $url = "{$this->apiUrl}/instance/connect/{$this->instanceName}";
        
        $headers = [
            'apikey: ' . $this->apiKey
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'response' => $responseData,
                'http_code' => $httpCode,
                'qrcode' => $responseData['qrcode'] ?? $responseData['base64'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseData['message'] ?? $responseData['error'] ?? 'Erro desconhecido',
                'response' => $responseData,
                'http_code' => $httpCode
            ];
        }
    }
}
