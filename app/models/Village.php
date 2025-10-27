<?php
/**
 * Model Village - Gerenciamento de Villages/Blocos
 */

class Village extends Model {
    protected $table = 'villages';
    
    /**
     * Buscar todas as villages
     */
    public function getAll() {
        return $this->findAll('name', 'ASC');
    }
    
    /**
     * Buscar villages ativas
     */
    public function getActive() {
        return $this->findWhere(['status' => 'ativo'], 'name', 'ASC');
    }
    
    /**
     * Buscar village com contagem de casas
     */
    public function getAllWithHouseCount() {
        $sql = "SELECT v.*, COUNT(h.id) as house_count 
                FROM {$this->table} v
                LEFT JOIN houses h ON v.id = h.village_id
                GROUP BY v.id
                ORDER BY v.name ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Buscar village por ID com contagem de casas
     */
    public function getByIdWithHouseCount($id) {
        $sql = "SELECT v.*, COUNT(h.id) as house_count 
                FROM {$this->table} v
                LEFT JOIN houses h ON v.id = h.village_id
                WHERE v.id = :id
                GROUP BY v.id
                LIMIT 1";
        
        return $this->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Verificar se village tem casas associadas
     */
    public function hasHouses($id) {
        $sql = "SELECT COUNT(*) as total FROM houses WHERE village_id = :id";
        $result = $this->queryOne($sql, ['id' => $id]);
        return $result['total'] > 0;
    }
    
    /**
     * Verificar se village tem condôminos associados
     */
    public function hasResidents($id) {
        $sql = "SELECT COUNT(*) as total FROM residents r
                INNER JOIN houses h ON r.house_id = h.id
                WHERE h.village_id = :id";
        $result = $this->queryOne($sql, ['id' => $id]);
        return $result['total'] > 0;
    }
}
