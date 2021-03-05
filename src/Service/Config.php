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
    private const FILE_GLOBAL = __DIR__ . '/../../config/config.ini';
    private const FILE_LOCAL = __DIR__ . '/../../config/config.local.ini';
    
    private array $configData;
    
    public function __construct()
    {
        $this->configData = $this->readConfig(self::FILE_GLOBAL, self::FILE_LOCAL);
    }
    
    /**
     * Get single config value by key
     *
     * @param  string      $key
     * @param  string|null $default
     * @return string
     */
    public function get(string $key, ?string $default = null): string
    {
        if (!array_key_exists($key, $this->configData)) {
            if ($default !== null) {
                return $default;
            } else {
                throw new Exception(sprintf('Config key "%s"not found', $key));
            }
        }
        
        return $this->configData[$key];
    }
    
    /**
     * Read global and local config files and return config as assoc array
     *
     * @param  string $globalPath
     * @param  string $localPath
     * @return array<string>
     */
    private function readConfig(string $globalPath, string $localPath): array
    {
        $global = parse_ini_file($globalPath);
        $local = [];
        if (file_exists($localPath)) {
            $local = parse_ini_file($localPath);
        }
        
        return array_merge($global, $local);
    }
}
