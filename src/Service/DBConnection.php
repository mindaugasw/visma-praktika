<?php

namespace App\Service;

use PDO;

class DBConnection
{
    private Config $config;
    
    private PDO $connection;
    
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    
    /**
     * @return PDO
     * @throws \Exception
     */
    private function connect(): PDO
    {
        if (!isset($this->connection))
        {
            $host = $this->config->get(Config::DB_HOST);
            $db = $this->config->get(Config::DB_NAME);
            $user = $this->config->get(Config::DB_USERNAME);
            $password = $this->config->get(Config::DB_PASSWORD);
            
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $this->connection = new PDO($dsn, $user, $password, $options);
        }
        
        return $this->connection;
    }
    
    /**
     * DB test method. Should return db version string
     * @return string
     */
    public function testDbConnection(): string
    {
        $connection = $this->connect();
        $query = $connection->query("SELECT VERSION()");
        return $query->fetch()["VERSION()"];
    }
    
    /**
     * Generic DB query, not returning results
     * @param string $query
     * @param array $params
     * @return bool was the operation successful?
     */
    public function query(string $query, array $params)
    {
        $connection = $this->connect();
        $statement = $connection->prepare($query);
        return $statement->execute($params);
    }
    
    /**
     * Fetch items and convert to given class objects
     * @param string $query
     * @param array $params
     * @param string $className class to convert items to
     * @return array
     */
    public function fetchClass(string $query, array $params, string $className): array
    {
        $connection = $this->connect();
        $statement = $connection->prepare($query);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_CLASS, $className);
    }
    
    /**
     * Insert new item and return its id
     * @param string $query
     * @param array $params
     * @return int Newly inserted item id
     */
    public function insert(string $query, array $params): int
    {
        $connection = $this->connect();
        $statement = $connection->prepare($query);
        $res = $statement->execute($params);
        
        if ($res === false)
            throw new \Exception("Database error occurred");
        
        return $connection->lastInsertId();
    }
    
    /**
     * Run query inside a transaction
     * @param string $query
     * @param array $params
     * @return bool Transaction success result
     */
    public function queryTransaction(string $query, array $params): bool
    {
        $connection = $this->connect();
        $connection->beginTransaction();
        $this->query($query, $params);
        return $connection->commit();
    }
}
