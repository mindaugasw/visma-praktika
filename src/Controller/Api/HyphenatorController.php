<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Service\Hyphenator\HyphenationHandler;
use App\Service\Response\JsonErrorResponse;
use App\Service\Response\JsonResponse;
use App\Service\Response\Response;

class HyphenatorController extends BaseController
{
    private HyphenationHandler $hyphenationHandler;
    
    public function __construct(HyphenationHandler $hyphenationHandler)
    {
        parent::__construct();
        $this->hyphenationHandler = $hyphenationHandler;
    }
    
    /**
     * Hyphenate a single word
     * Args: string $word
     *
     * @param  array $args
     * @return Response
     */
    public function singleWord_post(array $args): Response
    {
        $word = $this->getArgOrDefault($args, 'word');
        
        if (str_contains($word, ' ') || empty($word)) {
            return new JsonErrorResponse(statusCode: 400);
        }
        
        $wordResult = $this->hyphenationHandler->processOneWord($word);
        
        return new JsonResponse($wordResult);
    }
    
    /**
     * Hyphenate a block of text
     * Args: string $text
     *
     * @param  array $args
     * @return Response
     */
    public function text_post(array $args): Response
    {
        $text = $this->getArgOrDefault($args, 'text');
        
        if (empty($text)) {
            return new JsonErrorResponse();
        }
        
        $data = [
            'text' => $this->hyphenationHandler->processText($text) 
        ];
        
        return new JsonResponse($data);
    }
}
