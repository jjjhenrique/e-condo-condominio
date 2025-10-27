<?php
/**
 * Model House - Gerenciamento de Casas/Unidades
 */

class House extends Model {
    protected $table = 'houses';
    
    /**
     * Buscar casas ativas
     */
    public function getActive() {
        return $this->findWhere(['status' => 'ativo'], 'house_number', 'ASC');
    }
    
    /**
     * Buscar todas as casas com informações de village
     */
    public function getAllWithVillageInfo() {
        $sql = "SELECT h.*, v.name as village_name 
                FROM {$this->table} h
                INNER JOIN villages v ON h.village_id = v.id
                ORDER BY v.name ASC, h.house_number ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Buscar casas por village
     */
    public function getByVillage($villageId) {
        $sql = "SELECT h.*, v.name as village_name 
                FROM {$this->table} h
                INNER JOIN villages v ON h.village_id = v.id
                WHERE h.village_id = :village_id AND h.status = 'ativo'
                ORDER BY h.house_number ASC";
        
        return $this->query($sql, ['village_id' => $villageId]);
    }
    
    /**
     * Buscar casa por ID com informações de village
     */
    public function getByIdWithVillageInfo($id) {
        $sql = "SELECT h.*, v.name as village_name 
                FROM {$this->table} h
                INNER JOIN villages v ON h.village_id = v.id
                WHERE h.id = :id
                LIMIT 1";
        
        return $this->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Verificar se número da casa já existe na village
     */
    public function houseNumberExists($villageId, $houseNumber, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE village_id = :village_id AND house_number = :house_number";
        
        if ($excludeId) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':village_id', $villageId);
        $stmt->bindValue(':house_number', $houseNumber);
        
        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Verificar se casa tem condôminos associados
     */
    public function hasResidents($id) {
        $sql = "SELECT COUNT(*) as total FROM residents WHERE house_id = :id";
        $result = $this->queryOne($sql, ['id' => $id]);
        return $result['total'] > 0;
    }
    
    /**
     * Buscar casas ativas por village (para dropdown)
     */
    public function getActiveByVillage($villageId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE village_id = :village_id AND status = 'ativo'
                ORDER BY house_number ASC";
        
        return $this->query($sql, ['village_id' => $villageId]);
    }
    
    /**
     * Contar casas por village
     */
    public function countByVillage($villageId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE village_id = :village_id";
        $result = $this->queryOne($sql, ['village_id' => $villageId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Buscar casa por village e número
     */
    public function findByVillageAndNumber($villageId, $houseNumber) {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE village_id = :village_id AND house_number = :house_number LIMIT 1",
            ['village_id' => $villageId, 'house_number' => $houseNumber]
        );
    }
}
