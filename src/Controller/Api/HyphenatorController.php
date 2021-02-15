<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Service\App;
use App\Service\Hyphenator\HyphenationHandler;
use App\Service\Response\JsonErrorResponse;
use App\Service\Response\JsonResponse;

class HyphenatorController extends BaseController
{
    private HyphenationHandler $hyphenator;
    
    public function __construct(App $app)
    {
        parent::__construct();
        $this->hyphenator = $app->hyphenationHandler;
    }
    
    public function singleWord_post(array $args)
    {
        $word = $this->getArgOrDefault($args, 'word', isRequired: true);
        
        if (str_contains($word, ' ')) {
            return new JsonErrorResponse(statusCode: 400);
        }
        
        $wordResult = $this->hyphenator->processOneWord($word);
        
        return new JsonResponse($wordResult);
    }
    
    public function text_post(array $args)
    {
        
    }
}
