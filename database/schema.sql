-- ============================================
-- Sistema de Recebimento de Encomendas
-- Database Schema
-- ============================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS econdo_packages CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE econdo_packages;

-- ============================================
-- Tabela de Usuários
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'porteiro', 'administracao') NOT NULL DEFAULT 'porteiro',
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela de Villages/Blocos
-- ============================================
CREATE TABLE IF NOT EXISTS villages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela de Casas/Unidades
-- ============================================
CREATE TABLE IF NOT EXISTS houses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    village_id INT NOT NULL,
    house_number VARCHAR(20) NOT NULL,
    complement VARCHAR(100) DEFAULT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE,
    UNIQUE KEY unique_village_house (village_id, house_number),
    INDEX idx_village (village_id),
    INDEX idx_house_number (house_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela de Condôminos
-- ============================================
CREATE TABLE IF NOT EXISTS residents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    house_id INT,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE SET NULL,
    INDEX idx_cpf (cpf),
    INDEX idx_phone (phone),
    INDEX idx_full_name (full_name),
    INDEX idx_house (house_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela de Encomendas
-- ============================================
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    house_id INT NOT NULL,
    tracking_code VARCHAR(20) NOT NULL UNIQUE,
    current_location ENUM('portaria', 'administracao', 'retirada') NOT NULL DEFAULT 'portaria',
    status ENUM('pendente', 'transferida', 'retirada') NOT NULL DEFAULT 'pendente',
    observations TEXT,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    received_by INT,
    transferred_at TIMESTAMP NULL,
    transferred_by INT NULL,
    picked_up_at TIMESTAMP NULL,
    picked_up_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (transferred_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (picked_up_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tracking_code (tracking_code),
    INDEX idx_resident (resident_id),
    INDEX idx_house (house_id),
    INDEX idx_location (current_location),
    INDEX idx_status (status),
    INDEX idx_received_at (received_at),
    INDEX idx_picked_up_at (picked_up_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela de Histórico de Movimentações
-- ============================================
CREATE TABLE IF NOT EXISTS package_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    action ENUM('recebida', 'transferida', 'retirada') NOT NULL,
    from_location VARCHAR(50),
    to_location VARCHAR(50),
    user_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_package (package_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela de Notificações WhatsApp
-- ============================================
CREATE TABLE IF NOT EXISTS whatsapp_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    resident_id INT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('recebimento', 'retirada') NOT NULL,
    status ENUM('pendente', 'enviada', 'erro') NOT NULL DEFAULT 'pendente',
    response_data TEXT,
    error_message TEXT,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_package (package_id),
    INDEX idx_resident (resident_id),
    INDEX idx_status (status),
    INDEX idx_type (notification_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela de Logs de Sistema
-- ============================================
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela de Configurações do Sistema
-- ============================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Dados Iniciais
-- ============================================

-- Usuário administrador padrão (senha: admin123)
-- IMPORTANTE: Execute fix_passwords.php após importar este SQL para gerar hashes corretos
INSERT INTO users (username, password, full_name, email, role, status) VALUES
('admin', 'TEMP_PASSWORD', 'Administrador do Sistema', 'admin@econdo.com', 'admin', 'ativo'),
('porteiro1', 'TEMP_PASSWORD', 'Porteiro Principal', 'porteiro@econdo.com', 'porteiro', 'ativo'),
('adm1', 'TEMP_PASSWORD', 'Administração Interna', 'administracao@econdo.com', 'administracao', 'ativo');

-- Configurações da Evolution API (WhatsApp)
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('evolution_api_url', 'http://localhost:8080', 'URL da Evolution API'),
('evolution_api_key', '', 'API Key da Evolution API'),
('evolution_instance_name', '', 'Nome da instância na Evolution API'),
('whatsapp_enabled', '0', 'Habilitar/Desabilitar envio de notificações WhatsApp (0=desabilitado, 1=habilitado)'),
('system_name', 'E-Condo Packages', 'Nome do sistema'),
('packages_per_page', '20', 'Quantidade de encomendas por página'),
('backup_enabled', '1', 'Habilitar backup automático do banco de dados');

-- Villages de exemplo
INSERT INTO villages (name, description, status) VALUES
('Village A', 'Área residencial A', 'ativo'),
('Village B', 'Área residencial B', 'ativo'),
('Village C', 'Área residencial C', 'ativo');

-- Casas de exemplo para Village A
INSERT INTO houses (village_id, house_number, status) VALUES
(1, '101', 'ativo'),
(1, '102', 'ativo'),
(1, '103', 'ativo'),
(1, '104', 'ativo'),
(1, '105', 'ativo');

-- Condôminos de exemplo
INSERT INTO residents (full_name, cpf, phone, email, house_id, status) VALUES
('João da Silva', '123.456.789-00', '11987654321', 'joao.silva@email.com', 1, 'ativo'),
('Maria Santos', '987.654.321-00', '11976543210', 'maria.santos@email.com', 2, 'ativo'),
('Pedro Oliveira', '456.789.123-00', '11965432109', 'pedro.oliveira@email.com', 3, 'ativo');

-- ============================================
-- Views para Relatórios
-- ============================================

-- View: Encomendas com informações completas
CREATE OR REPLACE VIEW vw_packages_full AS
SELECT 
    p.id,
    p.tracking_code,
    p.current_location,
    p.status,
    p.observations,
    p.received_at,
    p.transferred_at,
    p.picked_up_at,
    r.id as resident_id,
    r.full_name as resident_name,
    r.cpf as resident_cpf,
    r.phone as resident_phone,
    r.email as resident_email,
    h.id as house_id,
    h.house_number,
    v.id as village_id,
    v.name as village_name,
    u1.full_name as received_by_name,
    u2.full_name as transferred_by_name,
    u3.full_name as picked_up_by_name
FROM packages p
INNER JOIN residents r ON p.resident_id = r.id
INNER JOIN houses h ON p.house_id = h.id
INNER JOIN villages v ON h.village_id = v.id
LEFT JOIN users u1 ON p.received_by = u1.id
LEFT JOIN users u2 ON p.transferred_by = u2.id
LEFT JOIN users u3 ON p.picked_up_by = u3.id;

-- View: Estatísticas do dia
CREATE OR REPLACE VIEW vw_daily_statistics AS
SELECT 
    DATE(received_at) as date,
    COUNT(*) as total_received,
    SUM(CASE WHEN current_location = 'portaria' THEN 1 ELSE 0 END) as at_portaria,
    SUM(CASE WHEN current_location = 'administracao' THEN 1 ELSE 0 END) as at_administracao,
    SUM(CASE WHEN status = 'retirada' THEN 1 ELSE 0 END) as picked_up
FROM packages
GROUP BY DATE(received_at);

-- ============================================
-- Stored Procedures
-- ============================================

DELIMITER //

-- Procedure: Gerar código único de rastreamento
CREATE PROCEDURE generate_tracking_code(OUT new_code VARCHAR(20))
BEGIN
    DECLARE code_exists INT DEFAULT 1;
    DECLARE temp_code VARCHAR(20);
    
    WHILE code_exists = 1 DO
        SET temp_code = CONCAT('PKG', LPAD(FLOOR(RAND() * 999999999), 9, '0'));
        SELECT COUNT(*) INTO code_exists FROM packages WHERE tracking_code = temp_code;
    END WHILE;
    
    SET new_code = temp_code;
END //

-- Procedure: Obter estatísticas do dashboard
CREATE PROCEDURE get_dashboard_stats(IN target_date DATE)
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM packages WHERE DATE(received_at) = target_date) as received_today,
        (SELECT COUNT(*) FROM packages WHERE current_location = 'portaria' AND status != 'retirada') as pending_portaria,
        (SELECT COUNT(*) FROM packages WHERE current_location = 'administracao' AND status != 'retirada') as pending_administracao,
        (SELECT COUNT(*) FROM packages WHERE DATE(picked_up_at) = target_date) as picked_up_today,
        (SELECT COUNT(*) FROM packages WHERE status = 'pendente') as total_pending,
        (SELECT COUNT(*) FROM packages WHERE status = 'transferida') as total_transferred,
        (SELECT COUNT(*) FROM packages WHERE status = 'retirada') as total_picked_up;
END //

DELIMITER ;

-- ============================================
-- Triggers
-- ============================================

DELIMITER //

-- Trigger: Registrar histórico ao receber encomenda
CREATE TRIGGER after_package_insert
AFTER INSERT ON packages
FOR EACH ROW
BEGIN
    INSERT INTO package_history (package_id, action, from_location, to_location, user_id, notes)
    VALUES (NEW.id, 'recebida', NULL, 'portaria', NEW.received_by, 'Encomenda recebida na portaria');
END //

-- Trigger: Registrar histórico ao transferir encomenda
CREATE TRIGGER after_package_transfer
AFTER UPDATE ON packages
FOR EACH ROW
BEGIN
    IF OLD.current_location != NEW.current_location AND NEW.current_location = 'administracao' THEN
        INSERT INTO package_history (package_id, action, from_location, to_location, user_id, notes)
        VALUES (NEW.id, 'transferida', OLD.current_location, NEW.current_location, NEW.transferred_by, 'Encomenda transferida para administração');
    END IF;
END //

-- Trigger: Registrar histórico ao retirar encomenda
CREATE TRIGGER after_package_pickup
AFTER UPDATE ON packages
FOR EACH ROW
BEGIN
    IF OLD.status != 'retirada' AND NEW.status = 'retirada' THEN
        INSERT INTO package_history (package_id, action, from_location, to_location, user_id, notes)
        VALUES (NEW.id, 'retirada', NEW.current_location, 'retirada', NEW.picked_up_by, 'Encomenda retirada pelo condômino');
    END IF;
END //

DELIMITER ;

-- ============================================
-- Índices adicionais para performance
-- ============================================

-- Índices compostos para queries comuns
CREATE INDEX idx_packages_location_status ON packages(current_location, status);
CREATE INDEX idx_packages_received_date ON packages(received_at, status);
CREATE INDEX idx_residents_house_status ON residents(house_id, status);

-- ============================================
-- Fim do Schema
-- ============================================
