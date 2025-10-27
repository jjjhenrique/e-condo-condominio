<?php
/**
 * Classe Database - Gerenciamento de Conexão com Banco de Dados
 * 
 * Implementa o padrão Singleton para garantir uma única conexão PDO
 */

class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Erro de conexão com o banco de dados: " . $e->getMessage());
        }
    }
    
    /**
     * Obter instância única da classe
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obter conexão PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prevenir clonagem do objeto
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialização do objeto
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
