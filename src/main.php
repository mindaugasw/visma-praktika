<?php
require_once(__DIR__.'/../autoload.php');

use App\Command\InteractiveInput;
use App\Command\TextBlockInput;
use App\Exception\NotImplementedException;
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
        throw new NotImplementedException();
    default:
        throw new Exception(sprintf('Unknown command "%s"', $command));
}
