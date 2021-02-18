<?php

//use App\Service\App;

require_once(__DIR__.'/../vendor/autoload.php');


//$app = new App();

$diContainer = new \App\DIContainer\Container();
$app = $diContainer->get(\App\Service\App::class);
die('asdf');


if (http_response_code() === false) {
    $app->commandManager->autoExecuteCommand();
} else {
    $app->router->route();
}
