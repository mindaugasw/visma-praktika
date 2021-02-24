<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Exception\BadRequestException;
use App\Service\Hyphenator\HyphenationHandler;
use App\Service\Response\ErrorResponse;
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
            //return new ErrorResponse(statusCode: 400);
            throw new BadRequestException('Found more than one word in input string. '
                                          .'Use appropriate method for text hyphenation.');
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
            //return new ErrorResponse();
            throw new BadRequestException('No words found in text.');
        }
        
        $data = [
            'text' => $this->hyphenationHandler->processText($text)
        ];
        
        return new JsonResponse($data);
    }
}
