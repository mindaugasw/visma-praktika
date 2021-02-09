<?php

namespace App\Service;

use PDO;

class DBConnection
{
    // Config keys
    const CFG_DB_HOST = "db_host";
    const CFG_DB_NAME = "db_name";
    const CFG_DB_USERNAME = "db_username";
    const CFG_DB_PASSWORD = "db_password";
    
    private Config $config;
    
    private PDO $connection;
    
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->connection = $this->connect();
    }
    
    /**
     * @return PDO
     * @throws \Exception
     */
    private function connect(): PDO
    {
        if (!isset($this->connection))
        {
            $host = $this->config->get(self::CFG_DB_HOST);
            $db = $this->config->get(self::CFG_DB_NAME);
            $user = $this->config->get(self::CFG_DB_USERNAME);
            $password = $this->config->get(self::CFG_DB_PASSWORD);
            
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
        $query = $this->connection->query("SELECT VERSION()");
        return $query->fetch()["VERSION()"];
    }
    
    /**
     * Generic DB query, not returning results
     * @param string $query
     * @param array $params
     * @return bool was the operation successful?
     */
    public function query(string $query, array $params = [])
    {
        $statement = $this->connection->prepare($query);
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
        $statement = $this->connection->prepare($query);
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
        $statement = $this->connection->prepare($query);
        $res = $statement->execute($params);
        
        if ($res === false)
            throw new \Exception("Database error occurred");
        
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction()
    {
        if (!$this->connection->beginTransaction())
            throw new \Exception('Could not begin transaction');
    }
    public function commitTransaction()
    {
        if (!$this->connection->commit())
            throw new \Exception('Could not commit transaction');
    }
    public function rollbackTransaction()
    {
        if (!$this->connection->rollBack())
            throw new \Exception('Could not rollback transaction');
    }
}
