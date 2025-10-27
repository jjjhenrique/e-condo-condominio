<?php
/**
 * Model User - Gerenciamento de Usuários
 */

class User extends Model {
    protected $table = 'users';
    
    /**
     * Buscar todos os usuários
     */
    public function getAll() {
        return $this->findAll('full_name', 'ASC');
    }
    
    /**
     * Buscar usuário por username
     */
    public function findByUsername($username) {
        return $this->findOneWhere(['username' => $username]);
    }
    
    /**
     * Buscar usuário por email
     */
    public function findByEmail($email) {
        return $this->findOneWhere(['email' => $email]);
    }
    
    /**
     * Criar novo usuário
     */
    public function create($data) {
        // Hash da senha
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], HASH_ALGORITHM, ['cost' => HASH_COST]);
        }
        
        return $this->insert($data);
    }
    
    /**
     * Atualizar usuário
     */
    public function updateUser($id, $data) {
        // Hash da senha se foi fornecida
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], HASH_ALGORITHM, ['cost' => HASH_COST]);
        } else {
            unset($data['password']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Verificar senha
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Autenticar usuário
     */
    public function authenticate($username, $password) {
        $user = $this->findByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        if ($user['status'] !== 'ativo') {
            return false;
        }
        
        if (!$this->verifyPassword($password, $user['password'])) {
            return false;
        }
        
        return $user;
    }
    
    /**
     * Buscar usuários ativos
     */
    public function getActiveUsers() {
        return $this->findWhere(['status' => 'ativo'], 'full_name', 'ASC');
    }
    
    /**
     * Buscar usuários por role
     */
    public function getUsersByRole($role) {
        return $this->findWhere(['role' => $role, 'status' => 'ativo'], 'full_name', 'ASC');
    }
    
    /**
     * Verificar se username já existe
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE username = :username";
        
        if ($excludeId) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $username);
        
        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
}
