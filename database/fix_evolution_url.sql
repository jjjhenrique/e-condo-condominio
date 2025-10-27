-- ============================================
-- Script para corrigir URL da Evolution API
-- ============================================

-- 1. Verificar configurações atuais
SELECT '=== CONFIGURAÇÕES ATUAIS ===' as '';
SELECT setting_key, setting_value, description 
FROM system_settings 
WHERE setting_key LIKE '%evolution%' OR setting_key = 'whatsapp_enabled';

-- 2. IMPORTANTE: Altere a URL abaixo para a URL correta da sua Evolution API
-- Exemplos:
-- 'http://192.168.1.100:8080'  (se estiver em outro IP local)
-- 'https://evolution.seudominio.com'  (se tiver domínio)
-- 'http://localhost:9000'  (se estiver em outra porta)

-- ALTERE AQUI A URL DA SUA EVOLUTION API:
UPDATE system_settings 
SET setting_value = 'https://api.sisunico.shop'  -- <<< ALTERE ESTA URL
WHERE setting_key = 'evolution_api_url';

-- ALTERE AQUI A SUA API KEY:
UPDATE system_settings 
SET setting_value = 'F11E1A8F5BC7-466F-AEC1-DFF592D81CB0'  -- <<< ALTERE ESTA API KEY
WHERE setting_key = 'evolution_api_key';

-- ALTERE AQUI O NOME DA SUA INSTÂNCIA:
UPDATE system_settings 
SET setting_value = 'e-condomínio'  -- <<< ALTERE O NOME DA INSTÂNCIA
WHERE setting_key = 'evolution_instance_name';

-- Habilitar WhatsApp
UPDATE system_settings 
SET setting_value = '1' 
WHERE setting_key = 'whatsapp_enabled';

-- 3. Verificar se foi atualizado
SELECT '=== CONFIGURAÇÕES ATUALIZADAS ===' as '';
SELECT setting_key, setting_value, description 
FROM system_settings 
WHERE setting_key LIKE '%evolution%' OR setting_key = 'whatsapp_enabled';
