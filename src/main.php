<?php
require_once(__DIR__.'/../autoload.php');

use App\Command\BatchProcess;
use App\Command\InteractiveInput;
use App\Command\TextBlockInput;
use App\Service\Config;
use App\Service\DBConnection;
use App\Service\FileHandler;
use App\Service\InputReader;
use App\Service\OutputWriter;
use App\Service\PsrLogger\Logger;
use App\Service\SyllablesAlgorithm;

$fileHandler = new FileHandler();
$logger = new Logger($fileHandler);
$reader = new InputReader();
$writer = new OutputWriter();
$alg = new SyllablesAlgorithm();
$config = new Config();
$db = new DBConnection($config);


$logger->info('Starting application, "{argv}"', ['argv' => implode(' ', $argv)]);

$command = $reader->getArg_command();
switch ($command) {
    case 'interactive':
        (new InteractiveInput($reader, $alg, $writer))->process();
        break;
    case 'text':
        (new TextBlockInput($reader, $alg, $fileHandler))->process();
        break;
    case 'batch':
        (new BatchProcess($reader, $alg, $fileHandler))->process();
        break;
    case 'db':
        (new \App\Command\DBTest($db, $config))->process();
        break;
    default:
        throw new Exception(sprintf('Unknown command "%s"', $command));
}
