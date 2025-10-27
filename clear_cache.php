<?php
/**
 * Script para limpar cache e sessão
 */

// Limpar sessão
session_start();
session_destroy();

// Limpar cache do OPcache se estiver habilitado
if (function_exists('opcache_reset')) {
    opcache_reset();
}

echo "✅ Cache e sessão limpos com sucesso!\n";
echo "Agora teste novamente o envio de mensagem.\n";
