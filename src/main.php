<?php

use App\Command\CommandManager;
use App\Service\DIContainer\Container;
use App\Service\Router;

require_once(__DIR__.'/../vendor/autoload.php');

$diContainer = new Container();

if (http_response_code() === false) {
    $diContainer->get(CommandManager::class)->autoExecuteCommand();
} else {
    $diContainer->get(Router::class)->route();
}
