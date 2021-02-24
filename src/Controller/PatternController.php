<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\HyphenationPatternRepository;
use App\Service\Response\HtmlResponse;
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
     * - limit, default 20
     * - page, default 1
     *
     * @param $args
     * @return Response
     */
    public function index_get($args): ?Response
    {
        $limit = intval($this->getArgOrDefault($args, 'limit', 20, false));
        $offset = (intval($this->getArgOrDefault($args, 'page', 1, false)) - 1) * $limit;
        
        $paginatedList = $this->patternRepo->getPaginated($limit, $offset);
        
        return new HtmlResponse('Page/Pattern/index', ['patterns' => $paginatedList]);
    }
}
