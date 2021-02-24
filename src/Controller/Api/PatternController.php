<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Exception\NotFoundException;
use App\Exception\NotImplementedException;
use App\Repository\HyphenationPatternRepository;
use App\Service\Response\ErrorResponse;
use App\Service\Response\JsonResponse;
use App\Service\Response\Response;

class PatternController extends BaseController
{
    private HyphenationPatternRepository $patternRepo;
    
    public function __construct(HyphenationPatternRepository $patternRepo)
    {
        parent::__construct();
        $this->patternRepo = $patternRepo;
    }
    
    /**
     * Get single pattern
     * Args:
     * - id, int, required. Pattern ID
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
     * Args:
     * - limit, int, optional, default 20
     * - page, int, optional, default 1
     *
     * @param  array $args
     * @return Response
     */
    public function list_get(array $args): Response
    {
        // TODO refactor to merge with Api\PatternController::index_get
        $limit = intval($this->getArgOrDefault($args, 'limit', 20, false));
        $offset = (intval($this->getArgOrDefault($args, 'page', 1, false)) - 1) * $limit;
    
        $paginatedList = $this->patternRepo->getPaginated($limit, $offset);
        
        return new JsonResponse($paginatedList);
    }
}
