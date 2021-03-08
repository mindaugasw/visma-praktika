<?php


namespace App\Controller;


use App\Exception\NotFoundException;
use App\Repository\WordResultRepository;
use App\Service\Response\ErrorResponse;
use App\Service\Response\HtmlResponse;
use App\Service\Response\Response;

class WordController extends BaseController
{
    public function __construct(private WordResultRepository $wordRepo)
    {
        parent::__construct();
    }

    /**
     * Get paginated list of words
     * Args:
     * - limit, int, optional, default 14
     * - page, int, optional, default 1
     *
     * @param $args
     * @return Response
     */
    public function index_get(array $args): Response
    {
        $limit = intval($this->getArgOrDefault($args, 'limit', 14, false));
        $offset = (intval($this->getArgOrDefault($args, 'page', 1, false)) - 1) * $limit;

        $paginatedList = $this->wordRepo->getPaginated($limit, $offset);

        return new HtmlResponse('Page/Word/index', ['words' => $paginatedList]);
    }

    /**
     * Args:
     * - word, int, required. Word ID
     *
     * @param $args
     * @return Response
     */
    public function view_get(array $args): Response
    {
        $id = intval($this->getArgOrDefault($args, 'word'));
        try {
            $pattern = $this->wordRepo->findOneById($id);
            return new HtmlResponse('Page/Word/view', ['word' => $pattern]);
        } catch (NotFoundException $ex) {
            return new ErrorResponse('Word not found', $ex::class, $ex->getStatus());
        }
    }
}