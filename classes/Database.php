<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function selectOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function insert($table, $data) {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));
        
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        
        $this->query($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $conditions) {
        $set = [];
        $conditionStr = [];
        $params = [];

        foreach ($data as $key => $value) {
            $set[] = "{$key} = ?";
            $params[] = $value;
        }

        foreach ($conditions as $key => $value) {
            $conditionStr[] = "{$key} = ?";
            $params[] = $value;
        }

        $setStr = implode(', ', $set);
        $conditionStr = implode(' AND ', $conditionStr);

        $sql = "UPDATE {$table} SET {$setStr} WHERE {$conditionStr}";
        
        return $this->query($sql, $params)->rowCount();
    }

    public function delete($table, $conditions) {
        $conditionStr = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $conditionStr[] = "{$key} = ?";
            $params[] = $value;
        }

        $conditionStr = implode(' AND ', $conditionStr);
        $sql = "DELETE FROM {$table} WHERE {$conditionStr}";
        
        return $this->query($sql, $params)->rowCount();
    }
} 