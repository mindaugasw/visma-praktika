<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Service\App;
use App\Service\ResponseCreator;

class WordsController extends BaseController
{
    private App $app;
    private ResponseCreator $responseCreator;
    
    public function __construct(App $app)
    {
        $this->responseCreator = $app->responseCreator;
    }
    
    public function get($args): void
    {
        
        $data = sprintf('Requested word: %s', $args['word']);
        
        $this->responseCreator->genericResponse($data);
        
    }
    
}
