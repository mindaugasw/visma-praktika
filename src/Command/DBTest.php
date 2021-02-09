<?php

namespace App\Command;

use App\Service\Config;
use App\Service\DBConnection;

class DBTest implements CommandInterface
{
    private DBConnection $db;
    private Config $config;
    
    public function __construct(DBConnection $db, Config $config)
    {
        $this->db = $db;
        $this->config = $config;
    }
    
    public function process(): void
    {
        echo $this->db->testDbConnection();
        
    }
}
