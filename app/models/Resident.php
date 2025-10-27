<?php
/**
 * Model Resident - Gerenciamento de Condôminos
 */

class Resident extends Model {
    protected $table = 'residents';
    
    /**
     * Buscar condômino por CPF
     */
    public function findByCPF($cpf) {
        // Remover formatação do CPF
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        $sql = "SELECT r.*, h.house_number, v.name as village_name, v.id as village_id 
                FROM {$this->table} r
                LEFT JOIN houses h ON r.house_id = h.id
                LEFT JOIN villages v ON h.village_id = v.id
                WHERE REPLACE(REPLACE(REPLACE(r.cpf, '.', ''), '-', ''), ' ', '') = :cpf
                LIMIT 1";
        
        return $this->queryOne($sql, ['cpf' => $cpf]);
    }
    
    /**
     * Buscar todos os condôminos com informações de casa/village
     */
    public function getAllWithHouseInfo() {
        $sql = "SELECT r.*, h.house_number, v.name as village_name, v.id as village_id 
                FROM {$this->table} r
                LEFT JOIN houses h ON r.house_id = h.id
                LEFT JOIN villages v ON h.village_id = v.id
                ORDER BY r.full_name ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Buscar condôminos ativos com informações de casa/village
     */
    public function getActiveWithHouseInfo() {
        $sql = "SELECT r.*, h.house_number, v.name as village_name, v.id as village_id 
                FROM {$this->table} r
                LEFT JOIN houses h ON r.house_id = h.id
                LEFT JOIN villages v ON h.village_id = v.id
                WHERE r.status = 'ativo'
                ORDER BY r.full_name ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Buscar condômino por ID com informações de casa/village
     */
    public function getByIdWithHouseInfo($id) {
        $sql = "SELECT r.*, h.house_number, v.name as village_name, v.id as village_id 
                FROM {$this->table} r
                LEFT JOIN houses h ON r.house_id = h.id
                LEFT JOIN villages v ON h.village_id = v.id
                WHERE r.id = :id
                LIMIT 1";
        
        return $this->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Buscar condôminos por village
     */
    public function getByVillage($villageId) {
        $sql = "SELECT r.*, h.house_number, v.name as village_name 
                FROM {$this->table} r
                INNER JOIN houses h ON r.house_id = h.id
                INNER JOIN villages v ON h.village_id = v.id
                WHERE v.id = :village_id AND r.status = 'ativo'
                ORDER BY r.full_name ASC";
        
        return $this->query($sql, ['village_id' => $villageId]);
    }
    
    /**
     * Buscar condôminos por casa
     */
    public function getByHouse($houseId) {
        $sql = "SELECT r.*, h.house_number, v.name as village_name 
                FROM {$this->table} r
                INNER JOIN houses h ON r.house_id = h.id
                INNER JOIN villages v ON h.village_id = v.id
                WHERE h.id = :house_id AND r.status = 'ativo'
                ORDER BY r.full_name ASC";
        
        return $this->query($sql, ['house_id' => $houseId]);
    }
    
    /**
     * Verificar se CPF já existe
     */
    public function cpfExists($cpf, $excludeId = null) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = :cpf";
        
        if ($excludeId) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cpf', $cpf);
        
        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Buscar condôminos com filtros
     */
    public function search($filters = []) {
        $sql = "SELECT r.*, h.house_number, v.name as village_name, v.id as village_id 
                FROM {$this->table} r
                LEFT JOIN houses h ON r.house_id = h.id
                LEFT JOIN villages v ON h.village_id = v.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['name'])) {
            $sql .= " AND r.full_name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }
        
        if (!empty($filters['cpf'])) {
            $cpf = preg_replace('/[^0-9]/', '', $filters['cpf']);
            $sql .= " AND REPLACE(REPLACE(REPLACE(r.cpf, '.', ''), '-', ''), ' ', '') LIKE :cpf";
            $params['cpf'] = '%' . $cpf . '%';
        }
        
        if (!empty($filters['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $filters['phone']);
            $sql .= " AND REPLACE(REPLACE(REPLACE(REPLACE(r.phone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE :phone";
            $params['phone'] = '%' . $phone . '%';
        }
        
        if (!empty($filters['village_id'])) {
            $sql .= " AND v.id = :village_id";
            $params['village_id'] = $filters['village_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params['status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY r.full_name ASC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Formatar telefone para WhatsApp (apenas números com código do país)
     */
    public function formatPhoneForWhatsApp($phone) {
        // Remover tudo exceto números
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Se não começar com 55 (código do Brasil), adicionar
        if (substr($phone, 0, 2) !== '55') {
            $phone = '55' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Contar condôminos por casa
     */
    public function countByHouse($houseId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE house_id = :house_id";
        $result = $this->queryOne($sql, ['house_id' => $houseId]);
        return $result['total'] ?? 0;
    }
}
