<?php

namespace App\Service;

class App
{
    // Config key
    private const CFG_APP_ENV = 'app_env';
    
    private bool $isCliEnv;
    /**
     * App environment (dev|prod)
     * @var string
     */
    private string $env;
    
    public function __construct(Config $config)
    {
        $this->isCliEnv = http_response_code() === false;
        $this->env = strtolower($config->get('app_env'));
    }
    
    public function isCliEnv(): bool
    {
        return $this->isCliEnv;
    }
    
    /**
     * Get application environment (dev|prod)
     * @return string
     */
    public function getEnv(): string
    {
        return $this->env;
    }
    
    public function isDevEnv(): bool
    {
        return $this->env === 'dev';
    }
    
    public function isProdEnv(): bool
    {
        return $this->env === 'prod';
    }
}
