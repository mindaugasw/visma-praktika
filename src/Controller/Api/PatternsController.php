<?php

namespace App\Controller\Api;

use App\Exception\NotImplementedException;
use App\Repository\HyphenationPatternRepository;
use App\Service\App;
use App\Service\Response\JsonErrorResponse;
use App\Service\Response\JsonResponse;
use App\Service\Response\ResponseHandler;

class PatternsController
{
    private HyphenationPatternRepository $patternRepo;
    private ResponseHandler $responseHandler;
    
    public function __construct(App $app)
    {
        $this->patternRepo = $app->patternRepo;
        $this->responseHandler = $app->responseHandler;
    }
    
    /**
     * Get single pattern
     * Args: string $pattern
     * @param array $args
     */
    public function get(array $args): void
    {        
        if (!isset($args['pattern'])) {
            $this->responseHandler->returnResponse(
                new JsonErrorResponse(statusCode: 400)
            );
        }
        
        $pattern = $this->patternRepo->findOne($args['pattern']);
        
        if ($pattern === null) {
            $this->responseHandler->returnResponse(
                new JsonErrorResponse(statusCode: 404)
            );
        }
        
        $this->responseHandler->returnResponse(
            new JsonResponse($pattern)
        );
    }
    
    public function list_get(array $args): void
    {
        throw new NotImplementedException();
    }
}
