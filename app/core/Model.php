<?php
/**
 * Classe Model - Classe base para todos os models
 * 
 * Fornece métodos comuns para interação com o banco de dados
 */

class Model {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todos os registros
     */
    public function findAll($orderBy = 'id', $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar registro por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Buscar registros com condições
     */
    public function findWhere($conditions, $orderBy = 'id', $order = 'ASC') {
        $where = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $whereClause = implode(' AND ', $where);
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY {$orderBy} {$order}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar um registro com condições
     */
    public function findOneWhere($conditions) {
        $where = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $whereClause = implode(' AND ', $where);
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Inserir registro
     */
    public function insert($data) {
        $fields = array_keys($data);
        $values = array_values($data);
        
        $fieldList = implode(', ', $fields);
        $placeholders = ':' . implode(', :', $fields);
        
        $sql = "INSERT INTO {$this->table} ({$fieldList}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualizar registro
     */
    public function update($id, $data) {
        $fields = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        
        $fieldList = implode(', ', $fields);
        $sql = "UPDATE {$this->table} SET {$fieldList} WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Deletar registro
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Contar registros
     */
    public function count($conditions = []) {
        if (empty($conditions)) {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->query($sql);
        } else {
            $where = [];
            $params = [];
            
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            
            $whereClause = implode(' AND ', $where);
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }
        
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Executar query customizada
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Executar query customizada e retornar um registro
     */
    public function queryOne($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Iniciar transação
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Confirmar transação
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Reverter transação
     */
    public function rollback() {
        return $this->db->rollback();
    }
}
