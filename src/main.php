<?php

use App\Service\App;

require_once(__DIR__.'/../vendor/autoload.php');

$app = new App();

if (http_response_code() === false) {
    //$app->autoChooseCommand();
    $app->commandManager->autoExecuteCommand();
} else {
    //$app->httpRoute();
    $app->router->route();
}
