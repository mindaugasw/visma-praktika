<?php

namespace App\Controller\Api;

use App\Service\App;

class HyphenatorController
{
    private App $app;
    
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    public function singleWord_post(array $args)
    {
        
    }
    
    public function text_post(array $args)
    {
        
    }
}
