<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Exception\NotFoundException;
use App\Exception\NotImplementedException;
use App\Repository\HyphenationPatternRepository;
use App\Service\Response\ErrorResponse;
use App\Service\Response\JsonResponse;
use App\Service\Response\Response;

class PatternsController extends BaseController
{
    private HyphenationPatternRepository $patternRepo;
    
    public function __construct(HyphenationPatternRepository $patternRepo)
    {
        parent::__construct();
        $this->patternRepo = $patternRepo;
    }
    
    /**
     * Get single pattern
     * Args: string $pattern
     *
     * @param  array $args
     * @return Response
     */
    public function get(array $args): Response
    {
        $patternId = intval($this->getArgOrDefault($args, 'id', isRequired: true));
        
        $pattern = $this->patternRepo->findOne($patternId);
        
        if ($pattern === null) {
            //return new ErrorResponse(statusCode: 404);
            throw new NotFoundException('Pattern not found');
        }
        
        return new JsonResponse($pattern);
    }
    
    /**
     * Get paginated list of patterns
     * Args: int $offset, int $limit
     *
     * @param  array $args
     * @return Response
     */
    public function list_get(array $args): Response
    {
        // TODO update to use PaginatedList
        throw new NotImplementedException();
        $offset = $this->getArgOrDefault($args, 'offset', 0, false);
        $limit = $this->getArgOrDefault($args, 'limit', 20, false);
        
        $patterns = $this->patternRepo->getPaginated($limit, $offset);
        
        return new JsonResponse($patterns);
    }
}
