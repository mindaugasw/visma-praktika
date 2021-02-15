<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Service\App;
use App\Service\Response\ResponseHandler;

// TODO delete
class WordsController extends BaseController
{
    private App $app;
    private ResponseHandler $responseCreator;
    
    public function __construct(App $app)
    {
        parent::__construct();
    }
    
    public function get($args): void
    {
        $data = sprintf('Requested word: %s', $args['word']);
        
        $this->responseCreator->genericResponse($data);
        
    }
    
}
