<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Exception\NotImplementedException;
use App\Repository\HyphenationPatternRepository;
use App\Service\App;
use App\Service\Response\JsonErrorResponse;
use App\Service\Response\JsonResponse;
use App\Service\Response\ResponseHandler;

class PatternsController extends BaseController
{
    private HyphenationPatternRepository $patternRepo;
    
    public function __construct(App $app)
    {
        parent::__construct($app->responseHandler);
        $this->patternRepo = $app->patternRepo;
    }
    
    /**
     * Get single pattern
     * Args: string $pattern
     * @param array $args
     */
    public function get(array $args): void
    {
        $patternArg = $this->getArgOrDefault($args, 'pattern', isRequired: true);
        
        $pattern = $this->patternRepo->findOne($patternArg);
        
        if ($pattern === null) {
            $this->responseHandler->returnResponse(
                new JsonErrorResponse(statusCode: 404)
            );
            return;
        }
        
        $this->responseHandler->returnResponse(
            new JsonResponse($pattern)
        );
    }
    
    public function list_get(array $args): void
    {
        $offset = $this->getArgOrDefault($args, 'offset', 0, false);
        $limit = $this->getArgOrDefault($args, 'limit', 20, false);
        
        $patterns = $this->patternRepo->getPaginated($limit, $offset);
        
        $this->responseHandler->returnResponse(
            new JsonResponse($patterns)
        );
    }
}
