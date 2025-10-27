<?php
/**
 * Model Package - Gerenciamento de Encomendas
 */

class Package extends Model {
    protected $table = 'packages';
    
    /**
     * Gerar código único de rastreamento
     */
    public function generateTrackingCode() {
        do {
            $code = 'PKG' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
            $exists = $this->findOneWhere(['tracking_code' => $code]);
        } while ($exists);
        
        return $code;
    }
    
    /**
     * Buscar encomenda por código de rastreamento
     */
    public function findByTrackingCode($code) {
        $sql = "SELECT p.*, 
                r.full_name as resident_name, r.cpf as resident_cpf, r.phone as resident_phone, r.email as resident_email,
                h.house_number, v.name as village_name,
                u1.full_name as received_by_name,
                u2.full_name as transferred_by_name,
                u3.full_name as picked_up_by_name
                FROM {$this->table} p
                INNER JOIN residents r ON p.resident_id = r.id
                INNER JOIN houses h ON p.house_id = h.id
                INNER JOIN villages v ON h.village_id = v.id
                LEFT JOIN users u1 ON p.received_by = u1.id
                LEFT JOIN users u2 ON p.transferred_by = u2.id
                LEFT JOIN users u3 ON p.picked_up_by = u3.id
                WHERE p.tracking_code = :code
                LIMIT 1";
        
        return $this->queryOne($sql, ['code' => $code]);
    }
    
    /**
     * Buscar todas as encomendas com informações completas
     */
    public function getAllWithFullInfo() {
        $sql = "SELECT p.*, 
                r.full_name as resident_name, r.cpf as resident_cpf, r.phone as resident_phone,
                h.house_number, v.name as village_name,
                u1.full_name as received_by_name,
                u2.full_name as transferred_by_name,
                u3.full_name as picked_up_by_name
                FROM {$this->table} p
                INNER JOIN residents r ON p.resident_id = r.id
                INNER JOIN houses h ON p.house_id = h.id
                INNER JOIN villages v ON h.village_id = v.id
                LEFT JOIN users u1 ON p.received_by = u1.id
                LEFT JOIN users u2 ON p.transferred_by = u2.id
                LEFT JOIN users u3 ON p.picked_up_by = u3.id
                ORDER BY p.received_at DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Buscar encomendas pendentes na portaria
     */
    public function getPendingAtPortaria() {
        $sql = "SELECT p.*, 
                r.full_name as resident_name, r.phone as resident_phone,
                h.house_number, v.name as village_name,
                u1.full_name as received_by_name
                FROM {$this->table} p
                INNER JOIN residents r ON p.resident_id = r.id
                INNER JOIN houses h ON p.house_id = h.id
                INNER JOIN villages v ON h.village_id = v.id
                LEFT JOIN users u1 ON p.received_by = u1.id
                WHERE p.current_location = 'portaria' AND p.status != 'retirada'
                ORDER BY p.received_at DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Buscar encomendas na administração
     */
    public function getPendingAtAdministracao() {
        $sql = "SELECT p.*, 
                r.full_name as resident_name, r.phone as resident_phone,
                h.house_number, v.name as village_name,
                u1.full_name as received_by_name,
                u2.full_name as transferred_by_name
                FROM {$this->table} p
                INNER JOIN residents r ON p.resident_id = r.id
                INNER JOIN houses h ON p.house_id = h.id
                INNER JOIN villages v ON h.village_id = v.id
                LEFT JOIN users u1 ON p.received_by = u1.id
                LEFT JOIN users u2 ON p.transferred_by = u2.id
                WHERE p.current_location = 'administracao' AND p.status != 'retirada'
                ORDER BY p.transferred_at DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Buscar encomendas retiradas
     */
    public function getPickedUp($limit = null) {
        $sql = "SELECT p.*, 
                r.full_name as resident_name, r.phone as resident_phone,
                h.house_number, v.name as village_name,
                u3.full_name as picked_up_by_name
                FROM {$this->table} p
                INNER JOIN residents r ON p.resident_id = r.id
                INNER JOIN houses h ON p.house_id = h.id
                INNER JOIN villages v ON h.village_id = v.id
                LEFT JOIN users u3 ON p.picked_up_by = u3.id
                WHERE p.status = 'retirada'
                ORDER BY p.picked_up_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        return $this->query($sql);
    }
    
    /**
     * Buscar encomendas com filtros
     */
    public function search($filters = []) {
        $sql = "SELECT p.*, 
                r.full_name as resident_name, r.cpf as resident_cpf, r.phone as resident_phone,
                h.house_number, v.name as village_name, v.id as village_id,
                u1.full_name as received_by_name,
                u2.full_name as transferred_by_name,
                u3.full_name as picked_up_by_name
                FROM {$this->table} p
                INNER JOIN residents r ON p.resident_id = r.id
                INNER JOIN houses h ON p.house_id = h.id
                INNER JOIN villages v ON h.village_id = v.id
                LEFT JOIN users u1 ON p.received_by = u1.id
                LEFT JOIN users u2 ON p.transferred_by = u2.id
                LEFT JOIN users u3 ON p.picked_up_by = u3.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['tracking_code'])) {
            $sql .= " AND p.tracking_code LIKE :tracking_code";
            $params['tracking_code'] = '%' . $filters['tracking_code'] . '%';
        }
        
        if (!empty($filters['resident_name'])) {
            $sql .= " AND r.full_name LIKE :resident_name";
            $params['resident_name'] = '%' . $filters['resident_name'] . '%';
        }
        
        if (!empty($filters['village_id'])) {
            $sql .= " AND v.id = :village_id";
            $params['village_id'] = $filters['village_id'];
        }
        
        if (!empty($filters['current_location'])) {
            $sql .= " AND p.current_location = :current_location";
            $params['current_location'] = $filters['current_location'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(p.received_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(p.received_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY p.received_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Busca rápida por código de rastreamento ou nome do condômino
     */
    public function quickSearch($searchTerm) {
        $searchValue = '%' . $searchTerm . '%';
        
        $sql = "SELECT p.*, 
                r.full_name as resident_name, r.cpf as resident_cpf, r.phone as resident_phone,
                h.house_number, v.name as village_name, v.id as village_id,
                u1.full_name as received_by_name,
                u2.full_name as transferred_by_name,
                u3.full_name as picked_up_by_name
                FROM {$this->table} p
                INNER JOIN residents r ON p.resident_id = r.id
                INNER JOIN houses h ON p.house_id = h.id
                INNER JOIN villages v ON h.village_id = v.id
                LEFT JOIN users u1 ON p.received_by = u1.id
                LEFT JOIN users u2 ON p.transferred_by = u2.id
                LEFT JOIN users u3 ON p.picked_up_by = u3.id
                WHERE (p.tracking_code LIKE :search1 
                   OR r.full_name LIKE :search2
                   OR r.cpf LIKE :search3
                   OR h.house_number LIKE :search4)
                ORDER BY p.received_at DESC";
        
        $params = [
            'search1' => $searchValue,
            'search2' => $searchValue,
            'search3' => $searchValue,
            'search4' => $searchValue
        ];
        
        return $this->query($sql, $params);
    }
    
    /**
     * Transferir encomenda para administração
     */
    public function transferToAdministracao($id, $userId) {
        $data = [
            'current_location' => 'administracao',
            'status' => 'transferida',
            'transferred_at' => date('Y-m-d H:i:s'),
            'transferred_by' => $userId
        ];
        
        return $this->update($id, $data);
    }
    
    /**
     * Marcar encomenda como retirada
     */
    public function markAsPickedUp($id, $userId) {
        $data = [
            'status' => 'retirada',
            'picked_up_at' => date('Y-m-d H:i:s'),
            'picked_up_by' => $userId
        ];
        
        return $this->update($id, $data);
    }
    
    /**
     * Obter estatísticas do dashboard
     */
    public function getDashboardStats($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $sql = "CALL get_dashboard_stats(:target_date)";
        return $this->queryOne($sql, ['target_date' => $date]);
    }
    
    /**
     * Obter histórico de uma encomenda
     */
    public function getHistory($packageId) {
        $sql = "SELECT ph.*, u.full_name as user_name
                FROM package_history ph
                LEFT JOIN users u ON ph.user_id = u.id
                WHERE ph.package_id = :package_id
                ORDER BY ph.created_at ASC";
        
        return $this->query($sql, ['package_id' => $packageId]);
    }
    
    /**
     * Contar encomendas por status
     */
    public function countByStatus($status) {
        return $this->count(['status' => $status]);
    }
    
    /**
     * Contar encomendas por localização
     */
    public function countByLocation($location) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE current_location = :location AND status != 'retirada'";
        $result = $this->queryOne($sql, ['location' => $location]);
        return $result['total'];
    }
    
    /**
     * Buscar encomendas recebidas hoje
     */
    public function getReceivedToday() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE DATE(received_at) = CURDATE()";
        $result = $this->queryOne($sql);
        return $result['total'];
    }
    
    /**
     * Buscar encomendas retiradas hoje
     */
    public function getPickedUpToday() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE DATE(picked_up_at) = CURDATE()";
        $result = $this->queryOne($sql);
        return $result['total'];
    }
}
