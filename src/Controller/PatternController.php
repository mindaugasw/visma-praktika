<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\NotFoundException;
use App\Repository\HyphenationPatternRepository;
use App\Service\Response\HtmlResponse;
use App\Service\Response\ErrorResponse;
use App\Service\Response\JsonResponse;
use App\Service\Response\Response;

class PatternController extends BaseController
{
    public function __construct(private HyphenationPatternRepository $patternRepo)
    {
        parent::__construct();
    }
    
    /**
     * Get paginated list of patterns
     * Args:
     * - limit, int, optional, default 14
     * - page, int, optional, default 1
     *
     * @param $args
     * @return Response
     */
    public function index_get($args): Response
    {
        $limit = intval($this->getArgOrDefault($args, 'limit', 14, false));
        $offset = (intval($this->getArgOrDefault($args, 'page', 1, false)) - 1) * $limit;
        
        $paginatedList = $this->patternRepo->getPaginated($limit, $offset);
        
        return new HtmlResponse('Page/Pattern/index', ['patterns' => $paginatedList]);
    }
    
    /**
     * Args:
     * - pattern, int, required. Pattern ID
     *
     * @param $args
     * @return Response
     */
    public function view_get($args): Response
    {
        $id = intval($this->getArgOrDefault($args, 'pattern', isRequired: true));
        try {
            $pattern = $this->patternRepo->findOne($id);
            return new HtmlResponse('Page/Pattern/view', ['pattern' => $pattern]);
        } catch (NotFoundException $ex) {
            return new ErrorResponse('Pattern not found', $ex::class, $ex->getStatus());
        }
    }
}
