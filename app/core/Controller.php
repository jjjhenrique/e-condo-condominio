<?php
/**
 * Classe Controller - Classe base para todos os controllers
 * 
 * Fornece métodos comuns para renderização de views e controle de acesso
 */

class Controller {
    
    /**
     * Renderizar view
     */
    protected function view($viewPath, $data = []) {
        // Extrair dados para variáveis
        extract($data);
        
        // Incluir header
        require_once BASE_PATH . 'app/views/layouts/header.php';
        
        // Incluir view específica
        $viewFile = BASE_PATH . 'app/views/' . $viewPath . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View não encontrada: {$viewPath}");
        }
        
        // Incluir footer
        require_once BASE_PATH . 'app/views/layouts/footer.php';
    }
    
    /**
     * Renderizar view parcial (sem header/footer)
     */
    protected function partial($viewPath, $data = []) {
        extract($data);
        
        $viewFile = BASE_PATH . 'app/views/' . $viewPath . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View não encontrada: {$viewPath}");
        }
    }
    
    /**
     * Retornar JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Verificar se usuário está autenticado
     */
    protected function requireAuth() {
        if (!isLoggedIn()) {
            setFlash('danger', 'Você precisa estar logado para acessar esta página.');
            redirect('/login.php');
        }
    }
    
    /**
     * Verificar se usuário tem permissão (role)
     */
    protected function requireRole($roles) {
        $this->requireAuth();
        
        if (!hasRole($roles)) {
            setFlash('danger', 'Você não tem permissão para acessar esta página.');
            redirect('/index.php');
        }
    }
    
    /**
     * Validar CSRF token
     */
    protected function validateCSRF($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
    
    /**
     * Gerar CSRF token
     */
    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validar dados de entrada
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $ruleList = explode('|', $rule);
            
            foreach ($ruleList as $r) {
                // Required
                if ($r === 'required' && empty($data[$field])) {
                    $errors[$field] = "O campo {$field} é obrigatório.";
                    break;
                }
                
                // Email
                if ($r === 'email' && !empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "O campo {$field} deve ser um e-mail válido.";
                    break;
                }
                
                // Numeric
                if ($r === 'numeric' && !empty($data[$field]) && !is_numeric($data[$field])) {
                    $errors[$field] = "O campo {$field} deve ser numérico.";
                    break;
                }
                
                // Min length
                if (strpos($r, 'min:') === 0) {
                    $min = (int)substr($r, 4);
                    if (!empty($data[$field]) && strlen($data[$field]) < $min) {
                        $errors[$field] = "O campo {$field} deve ter no mínimo {$min} caracteres.";
                        break;
                    }
                }
                
                // Max length
                if (strpos($r, 'max:') === 0) {
                    $max = (int)substr($r, 4);
                    if (!empty($data[$field]) && strlen($data[$field]) > $max) {
                        $errors[$field] = "O campo {$field} deve ter no máximo {$max} caracteres.";
                        break;
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Registrar log do sistema
     */
    protected function log($action, $entityType = null, $entityId = null, $description = null) {
        $logModel = new SystemLog();
        
        $data = [
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];
        
        $logModel->insert($data);
    }
}
