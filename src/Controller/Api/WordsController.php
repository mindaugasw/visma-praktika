<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Exception\EntityNotFoundException;
use App\Repository\WordResultRepository;
use App\Service\Response\JsonResponse;
use App\Service\Response\Response;

class WordsController extends BaseController
{
    private WordResultRepository $wordRepo;
    
    public function __construct(WordResultRepository $wordRepo)
    {
        parent::__construct();
        $this->wordRepo = $wordRepo;
    }
    
    /**
     * Delete word from hyphenated words DB
     * Args: int $id
     *
     * @param  array $args
     * @return Response
     */
    public function delete(array $args): Response
    {
        $id = intval($this->getArgOrDefault($args, 'id', isRequired: true));
        
        $wordResult = $this->wordRepo->findOneById($id);
        
        if ($wordResult === null) {
            throw new EntityNotFoundException();
        } else {
            $this->wordRepo->delete($wordResult);
            return new JsonResponse('');
        }
    }
}
