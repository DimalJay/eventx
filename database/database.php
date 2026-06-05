<?php
namespace database;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = $this->connect();
    }

    private function connect()
    {
        $config = require __DIR__ . '/../config/config.php';
        $host = $config["db"]["host"];
        $db = $config["db"]["dbname"];
        $username = $config["db"]["username"];
        $password = $config["db"]["password"];
        $charset = $config["db"]["charset"];

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $pdo = new PDO($dsn, $username, $password, $options);
            return $pdo;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int) $e->getCode());
        }
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function queryAll($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}