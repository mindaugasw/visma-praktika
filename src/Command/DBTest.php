<?php

namespace App\Command;

use App\Service\Config;

class DBTest implements CommandInterface
{
    /**
     * @var Config
     */
    private Config $config;
    
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    
    public function process(): void
    {
        echo $this->config->get(Config::DB_NAME);
        
    }
}
