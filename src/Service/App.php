<?php

namespace App\Service;

class App
{
    
    private bool $isCliEnv;
    
    public function __construct()
    {
        $this->isCliEnv = http_response_code() === false;
    }
    
    /**
     * @return bool
     */
    public function isCliEnv(): bool
    {
        return $this->isCliEnv;
    }
}
