<?php

//use App\Service\App;
use App\Command\CommandManager;
//use App\DIContainer\Container;
use App\Service\Router;

require_once(__DIR__.'/../vendor/autoload.php');


//$app = new App();

//$diContainer = new Container();
$diContainer = new \App\DIContainer\ContainerV2();
//$app = $diContainer->get(\App\Service\App::class);
//die('asdf');


if (http_response_code() === false) {
    //$app->commandManager->autoExecuteCommand();
    $diContainer->get(CommandManager::class)->autoExecuteCommand();
} else {
    //$app->router->route();
    $diContainer->get(Router::class)->route();
}
