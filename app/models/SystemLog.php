<?php
/**
 * Model SystemLog - Gerenciamento de Logs do Sistema
 */

class SystemLog extends Model {
    protected $table = 'system_logs';
    
    /**
     * Buscar logs recentes
     */
    public function getRecent($limit = 100) {
        $sql = "SELECT sl.*, u.full_name as user_name, u.username
                FROM {$this->table} sl
                LEFT JOIN users u ON sl.user_id = u.id
                ORDER BY sl.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar logs por usuário
     */
    public function getByUser($userId, $limit = 100) {
        $sql = "SELECT sl.*, u.full_name as user_name, u.username
                FROM {$this->table} sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE sl.user_id = :user_id
                ORDER BY sl.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar logs por ação
     */
    public function getByAction($action, $limit = 100) {
        $sql = "SELECT sl.*, u.full_name as user_name, u.username
                FROM {$this->table} sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE sl.action = :action
                ORDER BY sl.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':action', $action);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar logs por período
     */
    public function getByDateRange($dateFrom, $dateTo, $limit = 1000) {
        $sql = "SELECT sl.*, u.full_name as user_name, u.username
                FROM {$this->table} sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE DATE(sl.created_at) BETWEEN :date_from AND :date_to
                ORDER BY sl.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':date_from', $dateFrom);
        $stmt->bindValue(':date_to', $dateTo);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Limpar logs antigos (mais de X dias)
     */
    public function cleanOldLogs($days = 90) {
        $sql = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Buscar logs com filtros
     */
    public function search($filters = []) {
        $sql = "SELECT sl.*, u.full_name as user_name, u.username
                FROM {$this->table} sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND sl.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND sl.action = :action";
            $params['action'] = $filters['action'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(sl.created_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(sl.created_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY sl.created_at DESC LIMIT 500";
        
        return $this->query($sql, $params);
    }
}
