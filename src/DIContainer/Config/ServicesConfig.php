<?php

namespace App\DIContainer\Config;

use App\Service\App;
use App\Service\Router;

class ServicesConfig
{
    public static function getServicesConfig(): array
    {
        // array keys
        $CLASS = 'class';
        $ARGUMENTS = 'arguments';
        $CALLS = 'calls';
        
        return [
            App::class => [
                $CLASS => App::class,
                $ARGUMENTS => []
            ],
            Router::class => [
                $CLASS => Router::class,
                $ARGUMENTS => [
                    
                ]
            ],
        ];
    }
}