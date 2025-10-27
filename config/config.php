<?php
/**
 * Configurações do Sistema E-Condo Packages
 * 
 * Este arquivo contém todas as configurações necessárias para o funcionamento do sistema.
 * Ajuste os valores conforme seu ambiente.
 */

// ============================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ============================================
define('DB_HOST', '91.98.206.128:3306');
define('DB_NAME', 'econdo_packages');
define('DB_USER', 'joaoh');
define('DB_PASS', '@H3nrique0');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// CONFIGURAÇÕES DO SISTEMA
// ============================================
define('SITE_NAME', 'E-Condo Packages');
define('SITE_URL', 'https://econdo.sisunico.shop');
define('BASE_PATH', __DIR__ . '/../');

// ============================================
// CONFIGURAÇÕES DE SESSÃO
// ============================================
define('SESSION_NAME', 'econdo_session');
define('SESSION_LIFETIME', 7200); // 2 horas em segundos

// ============================================
// CONFIGURAÇÕES DE TIMEZONE
// ============================================
date_default_timezone_set('America/Sao_Paulo');

// ============================================
// CONFIGURAÇÕES DE ERRO (DESENVOLVIMENTO)
// ============================================
define('ENVIRONMENT', 'production'); // development ou production

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================
// CONFIGURAÇÕES DE UPLOAD
// ============================================
define('UPLOAD_MAX_SIZE', 5242880); // 5MB em bytes
define('UPLOAD_PATH', BASE_PATH . 'uploads/');

// ============================================
// CONFIGURAÇÕES DE PAGINAÇÃO
// ============================================
define('ITEMS_PER_PAGE', 20);

// ============================================
// CONFIGURAÇÕES DE SEGURANÇA
// ============================================
define('HASH_ALGORITHM', PASSWORD_DEFAULT);
define('HASH_COST', 10);

// ============================================
// CONFIGURAÇÕES DO WHATSAPP API (EVOLUTION API)
// ============================================
// Estas configurações podem ser alteradas pelo painel administrativo
// ou definidas diretamente aqui

// URL base da Evolution API (sem barra no final)
define('EVOLUTION_API_URL', 'http://localhost:8080');

// Token de autenticação da Evolution API (API Key global)
// Este token será carregado do banco de dados
// Caso queira definir aqui diretamente, descomente a linha abaixo:
// define('EVOLUTION_API_KEY', 'seu_api_key_aqui');

// Nome da instância do WhatsApp na Evolution API
// Será carregado do banco de dados
// Caso queira definir aqui diretamente, descomente a linha abaixo:
// define('EVOLUTION_INSTANCE_NAME', 'nome_da_instancia');

// ============================================
// AUTOLOAD DE CLASSES
// ============================================
spl_autoload_register(function ($class) {
    $directories = [
        BASE_PATH . 'app/models/',
        BASE_PATH . 'app/controllers/',
        BASE_PATH . 'app/core/',
        BASE_PATH . 'app/helpers/',
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ============================================
// INICIAR SESSÃO
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
    
    // Configurar tempo de vida da sessão
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_LIFETIME)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

// ============================================
// HELPERS GLOBAIS
// ============================================

/**
 * Redirecionar para uma URL
 */
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit;
}

/**
 * Verificar se usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obter dados do usuário logado
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'email' => $_SESSION['email'] ?? null,
    ];
}

/**
 * Verificar permissão por role
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['role'] ?? '', $roles);
}

/**
 * Sanitizar string
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Formatar data brasileira
 */
function formatDateBR($date) {
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Formatar CPF
 */
function formatCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) return $cpf;
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

/**
 * Formatar telefone
 */
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) == 11) {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
    } elseif (strlen($phone) == 10) {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
    }
    return $phone;
}

/**
 * Gerar mensagem flash
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obter e limpar mensagem flash
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Debug helper
 */
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}
