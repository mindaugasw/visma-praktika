<?php

namespace App\Service;

use Exception;

/**
 * Provides access to config files.
 * Config should be stored in config.ini (global) and config.local.ini (local machine).
 * Local config will overwrite global config if same keys are found.
 *
 * @package App\Service
 */
class Config
{
    const FILE_GLOBAL = __DIR__.'/../../config.ini';
    const FILE_LOCAL = __DIR__.'/../../config.local.ini';
    
    // Config keys
    const DB_HOST = "db_host";
    const DB_NAME = "db_name";
    const DB_USERNAME = "db_username";
    const DB_PASSWORD = "db_password";
    
    private array $configData;
    
    public function __construct()
    {
        $this->configData = $this->readConfig(self::FILE_GLOBAL, self::FILE_LOCAL);
    }
    
    /**
     * Get single config value by key
     *
     * @param string $key
     * @return string
     * @throws Exception
     */
    public function get(string $key): string
    {
        if (!array_key_exists($key, $this->configData))
            throw new Exception(sprintf('Config key "%s"not found', $key));
        
        return $this->configData[$key];
    }
    
    /**
     * Read global and local config files and return config as assoc array
     * @param string $globalPath
     * @param string $localPath
     * @return array<string>
     */
    private function readConfig(string $globalPath, string $localPath): array
    {
        $global = parse_ini_file($globalPath);
        $local = [];
        if (file_exists($localPath))
            $local = parse_ini_file($localPath);
        
        return array_merge($global, $local);
    }
}
