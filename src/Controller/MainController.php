<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\BaseController;
use App\Service\Response\HtmlResponse;
use App\Service\Response\Response;

class MainController extends BaseController
{
    public function index_get(): Response
    {
        return new HtmlResponse('Page/Hyphenator/index');
    }
}
