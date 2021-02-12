<?php

namespace App\Service;

class Router
{
    private App $app;
    
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    public function route()
    {
        $url = parse_url($_SERVER['REQUEST_URI']); // contains url['path'], url['query']
        $controller = explode('/', $url['path']);
        $controller = array_filter($controller, function ($pathElement) {
            return !empty($pathElement);
        });
        $controller = array_map(function ($pathElement) {
            return ucfirst($pathElement); 
        }, $controller);
        
        $controller = sprintf('App\\Controller\\%sController', implode('\\', $controller));
    
        parse_str($url['query'], $queryArgs);
        
        $method = '';
        if (isset($queryArgs['method']))
            $method = $queryArgs['method'];
        else
            $method = strtolower($_SERVER['REQUEST_METHOD']);
        
        (new $controller($this->app))->$method($queryArgs);
    }
}
