-- ============================================
-- Limpar notificações de QR Code com "erro" de URL local
-- ============================================

-- Essas não são erros reais, apenas avisos de que o QR Code
-- não foi enviado como imagem porque a URL é local

-- Ver quantas notificações serão removidas
SELECT COUNT(*) as total_para_remover
FROM whatsapp_notifications 
WHERE notification_type = 'qrcode' 
  AND status = 'erro' 
  AND error_message LIKE '%URL local%';

-- Remover as notificações
DELETE FROM whatsapp_notifications 
WHERE notification_type = 'qrcode' 
  AND status = 'erro' 
  AND error_message LIKE '%URL local%';

-- Verificar se foi removido
SELECT '=== Notificações restantes ===' as '';
SELECT notification_type, status, COUNT(*) as total
FROM whatsapp_notifications
GROUP BY notification_type, status;
