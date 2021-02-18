<?php

namespace App\Service\DB;

use App\Service\Config;
use Exception;
use PDO;
use Psr\Log\LoggerInterface;

class DBConnection
{
    // Config keys
    const CFG_DB_HOST = "db_host";
    const CFG_DB_NAME = "db_name";
    const CFG_DB_USERNAME = "db_username";
    const CFG_DB_PASSWORD = "db_password";
    
    private Config $config;
    
    private PDO $connection;
    private LoggerInterface $logger;
    
    public function __construct(Config $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->connection = $this->connect();
    }
    
    /**
     * @return PDO
     * @throws Exception
     */
    private function connect(): PDO
    {
        if (!isset($this->connection)) {
            $host = $this->config->get(self::CFG_DB_HOST);
            $db = $this->config->get(self::CFG_DB_NAME);
            $user = $this->config->get(self::CFG_DB_USERNAME);
            $password = $this->config->get(self::CFG_DB_PASSWORD);
            
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $this->connection = new PDO($dsn, $user, $password, $options);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); // TODO remove in prod?
        }
        
        return $this->connection;
    }
    
    /**
     * DB test method. Should return db version string
     *
     * @return string
     */
    public function testDbConnection(): string
    {
        $query = $this->connection->query("SELECT VERSION()");
        return $query->fetch()["VERSION()"];
    }
    
    /**
     * Generic DB query, not returning results
     *
     * @param  string $query
     * @param  array  $params
     * @return bool was the operation successful?
     */
    public function query(string $query, array $params = [])
    {
        $statement = $this->connection->prepare($query);
        return $statement->execute($params);
    }
    
    /**
     * Fetch items and convert to given class objects
     *
     * @param  string $query
     * @param  array  $params
     * @param  string $className class to convert items to
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
     *
     * @param  string $query
     * @param  array  $params
     * @return int Newly inserted item id
     */
    public function insert(string $query, array $params): int
    {
        $statement = $this->connection->prepare($query);
        $res = $statement->execute($params);
        
        if ($res === false) {
            throw new Exception("Database error occurred");
        }
        
        return $this->connection->lastInsertId();
    }
    
    public function getNextAutoIncrementId(string $tableName): int
    {
        /*$sql = sprintf(
           'SELECT `AUTO_INCREMENT`
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = \'%s\'
            AND TABLE_NAME = \'%s\'',
            $this->config->get(self::CFG_DB_NAME),
            $tableName
        );*/
        
        $sql = (new QueryBuilder())
            ->select('AUTO_INCREMENT')
            ->from('INFORMATION_SCHEMA.TABLES')
            ->where(
                sprintf(
                    'TABLE_SCHEMA = \'%s\' AND TABLE_NAME = \'%s\'',
                    $this->config->get(self::CFG_DB_NAME),
                    $tableName
                )
            )
            ->getQuery();
        
        $statement = $this->connection->prepare($sql);
        if (!$statement->execute()) {
            throw new Exception();
        }
        $nextId = $statement->fetch(PDO::FETCH_NUM)[0];
        if ($nextId === false) {
            throw new Exception();
        }
        return $nextId;
    }
    
    public function beginTransaction()
    {
        if ($this->connection->inTransaction()) {
            $this->logger->warning('Attempted to begin new transaction while already in a transaction');
        } else if (!$this->connection->beginTransaction()) {
            throw new Exception('Could not begin transaction');
        }
    }
    
    public function commitTransaction()
    {
        if (!$this->connection->inTransaction()) {
            $this->logger->warning('Attempted to commit transaction without starting it');
        } else if (!$this->connection->commit()) {
            throw new Exception('Could not commit transaction');
        }
    }
    
    public function rollbackTransaction()
    {
        if (!$this->connection->inTransaction()) {
            throw new Exception('Attempted to rollback transaction without starting it');
        } else if (!$this->connection->rollBack()) {
            throw new Exception('Could not rollback transaction');
        }
    }
}
