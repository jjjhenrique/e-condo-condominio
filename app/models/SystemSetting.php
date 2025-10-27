<?php
/**
 * Model SystemSetting - Gerenciamento de Configurações do Sistema
 */

class SystemSetting extends Model {
    protected $table = 'system_settings';
    
    /**
     * Buscar configuração por chave
     */
    public function get($key, $default = null) {
        $setting = $this->findOneWhere(['setting_key' => $key]);
        
        if (!$setting) {
            return $default;
        }
        
        return $setting['setting_value'];
    }
    
    /**
     * Definir configuração
     */
    public function set($key, $value, $description = null) {
        $existing = $this->findOneWhere(['setting_key' => $key]);
        
        if ($existing) {
            $data = ['setting_value' => $value];
            if ($description !== null) {
                $data['description'] = $description;
            }
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert([
                'setting_key' => $key,
                'setting_value' => $value,
                'description' => $description
            ]);
        }
    }
    
    /**
     * Buscar múltiplas configurações
     */
    public function getMultiple($keys) {
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $sql = "SELECT setting_key, setting_value FROM {$this->table} WHERE setting_key IN ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($keys);
        
        $results = $stmt->fetchAll();
        $settings = [];
        
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * Buscar todas as configurações
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY setting_key ASC";
        return $this->query($sql);
    }
    
    /**
     * Verificar se WhatsApp está habilitado
     */
    public function isWhatsAppEnabled() {
        return $this->get('whatsapp_enabled', '0') === '1';
    }
    
    /**
     * Obter configurações do WhatsApp (Evolution API)
     */
    public function getWhatsAppConfig() {
        $keys = ['evolution_api_key', 'evolution_instance_name', 'whatsapp_enabled'];
        $settings = $this->getMultiple($keys);
        
        return [
            'api_key' => $settings['evolution_api_key'] ?? '',
            'instance_name' => $settings['evolution_instance_name'] ?? '',
            'enabled' => ($settings['whatsapp_enabled'] ?? '0') === '1',
            // Manter compatibilidade com código antigo
            'token' => $settings['evolution_api_key'] ?? '',
            'phone_id' => $settings['evolution_instance_name'] ?? ''
        ];
    }
    
    /**
     * Atualizar configurações do WhatsApp (Evolution API)
     */
    public function updateWhatsAppConfig($apiKey, $instanceName, $enabled) {
        $this->set('evolution_api_key', $apiKey, 'API Key da Evolution API');
        $this->set('evolution_instance_name', $instanceName, 'Nome da instância na Evolution API');
        $this->set('whatsapp_enabled', $enabled ? '1' : '0', 'WhatsApp habilitado');
        
        return true;
    }
    
    /**
     * Obter URL da Evolution API
     */
    public function getEvolutionApiUrl() {
        return $this->get('evolution_api_url', EVOLUTION_API_URL);
    }
    
    /**
     * Atualizar URL da Evolution API
     */
    public function setEvolutionApiUrl($url) {
        return $this->set('evolution_api_url', $url, 'URL da Evolution API');
    }
}
