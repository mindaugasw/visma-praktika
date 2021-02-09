<?php
require_once(__DIR__.'/../autoload.php');

use App\Command\BatchProcess;
use App\Command\DBTest;
use App\Command\ImportData;
use App\Command\InteractiveInput;
use App\Command\TextBlockInput;
use App\Repository\HyphenationPatternRepository;
use App\Service\ArgsParser;
use App\Service\Config;
use App\Service\DBConnection;
use App\Service\FileHandler;
use App\Service\InputReader;
use App\Service\OutputWriter;
use App\Service\PsrLogger\Logger;
use App\Service\SyllablesAlgorithm;

$fileHandler = new FileHandler();
$config = new Config();
$logger = new Logger($fileHandler, $config);
$argsParser = new ArgsParser();
$reader = new InputReader($argsParser);
$writer = new OutputWriter();
$alg = new SyllablesAlgorithm();
$db = new DBConnection($config);
$patternRepo = new HyphenationPatternRepository($db);


$logger->debug('Starting application, "%s"', [implode(' ', $argv)]);

$argsParser->addArgConfig('command', 'c', true);
$argsParser->addArgConfig('method', 'm', false, ['array', 'tree']);
$command = $argsParser->get('command');

switch ($command) {
    case 'interactive':
        (new InteractiveInput($reader, $argsParser, $alg, $writer))->process();
        break;
    case 'text':
        (new TextBlockInput($reader, $argsParser, $alg, $fileHandler))->process();
        break;
    case 'batch':
        (new BatchProcess($reader, $alg, $fileHandler))->process();
        break;
    case 'db':
        (new DBTest($db, $config))->process(); // TODO remove
        break;
    case 'import':
        (new ImportData($db, $argsParser, $reader, $logger, $patternRepo))->process();
        break;
    default:
        throw new Exception(sprintf('Unknown command "%s"', $command));
}
