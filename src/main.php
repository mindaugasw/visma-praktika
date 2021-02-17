<?php

use App\Service\App;

require_once(__DIR__.'/../vendor/autoload.php');

$app = new App();

if (http_response_code() === false) {
    $app->autoChooseCommand();
} else {
    $app->httpRoute();
}
