<?php

use App\Service\App;

require_once(__DIR__.'/../autoload.php');

$app = new App();

if (http_response_code() === false)
    $app->autoChooseCommand();
else
    $app->httpRoute();
