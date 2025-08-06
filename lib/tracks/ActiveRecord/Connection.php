<?php

namespace Tracks\ActiveRecord;

use PDO;
use PDOStatement;

class Connection
{
    private ?PDO $pdo = null;
    private array $config = [];
    
    public function __construct()
    {
        $this->loadConfig();
        $this->connect();
    }
    
    private function loadConfig(): void
    {
        $configFile = dirname(__DIR__, 3) . '/config/database.php';
        if (file_exists($configFile)) {
            $configs = require $configFile;
            $env = $_ENV['TRACKS_ENV'] ?? 'development';
            $this->config = $configs[$env] ?? [];
        }
    }
    
    private function connect(): void
    {
        $adapter = $this->config['adapter'] ?? 'mysql';
        $host = $this->config['host'] ?? 'localhost';
        $database = $this->config['database'] ?? '';
        $username = $this->config['username'] ?? 'root';
        $password = $this->config['password'] ?? '';
        $port = $this->config['port'] ?? 3306;
        
        if ($adapter === 'sqlite') {
            $dsn = "sqlite:$database";
            $this->pdo = new PDO($dsn);
        } else {
            $dsn = "$adapter:host=$host;port=$port;dbname=$database;charset=utf8mb4";
            $this->pdo = new PDO($dsn, $username, $password);
        }
        
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }
    
    public function commit(): bool
    {
        return $this->pdo->commit();
    }
    
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
    
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}