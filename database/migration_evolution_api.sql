-- ============================================
-- Migração para Evolution API
-- Data: 22/10/2025
-- Descrição: Atualiza configurações do WhatsApp para usar Evolution API
-- ============================================

-- Remover configurações antigas da Meta Cloud API (se existirem)
DELETE FROM system_settings WHERE setting_key IN ('whatsapp_api_token', 'whatsapp_phone_id');

-- Adicionar novas configurações da Evolution API
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('evolution_api_url', 'http://localhost:8080', 'URL da Evolution API'),
('evolution_api_key', '', 'API Key da Evolution API'),
('evolution_instance_name', '', 'Nome da instância na Evolution API')
ON DUPLICATE KEY UPDATE 
    description = VALUES(description);

-- Manter a configuração de habilitação (se não existir, criar)
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('whatsapp_enabled', '0', 'Habilitar/Desabilitar envio de notificações WhatsApp (0=desabilitado, 1=habilitado)')
ON DUPLICATE KEY UPDATE 
    description = VALUES(description);

-- ============================================
-- Verificação
-- ============================================
-- Execute esta query para verificar as configurações:
-- SELECT * FROM system_settings WHERE setting_key LIKE '%evolution%' OR setting_key = 'whatsapp_enabled';
