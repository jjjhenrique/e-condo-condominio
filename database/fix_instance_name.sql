-- Teste 1: Nome sem acento
UPDATE system_settings 
SET setting_value = 'e-condominio' 
WHERE setting_key = 'evolution_instance_name';

-- Verificar
SELECT setting_key, setting_value FROM system_settings WHERE setting_key = 'evolution_instance_name';

-- OU se o nome for diferente, altere aqui:
-- UPDATE system_settings SET setting_value = 'NOME_CORRETO_AQUI' WHERE setting_key = 'evolution_instance_name';
