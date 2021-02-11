<?php
require_once(__DIR__.'/../autoload.php');

use App\Command\BatchProcess;
use App\Command\DBTest;
use App\Command\ImportData;
use App\Command\InteractiveInput;
use App\Command\TextBlockInput;
use App\Repository\HyphenationPatternRepository;
use App\Repository\WordResultRepository;
use App\Repository\WordToPatternRepository;
use App\Service\ArgsHandler;
use App\Service\Config;
use App\Service\DBConnection;
use App\Service\FileHandler;
use App\Service\InputReader;
use App\Service\OutputWriter;
use App\Service\PsrLogger\Logger;
use App\Service\Hyphenator;

$fileHandler = new FileHandler();
$config = new Config();
$logger = new Logger($fileHandler, $config);
$argsHandler = new ArgsHandler();
$db = new DBConnection($config, $logger);
$patternRepo = new HyphenationPatternRepository($db);
$wtpRepo = new WordToPatternRepository($db);
$wordRepo = new WordResultRepository($db, $wtpRepo, $logger);
$reader = new InputReader($argsHandler, $logger, $patternRepo);
$writer = new OutputWriter();
$hyphenator = new Hyphenator();

$logger->debug('Starting application, "%s"', [implode(' ', $argv)]);

$argsHandler->addArgConfig('command', 'c', true);
$argsHandler->addArgConfig('method', 'm', false, ['array', 'tree']);
$command = $argsHandler->get('command');

switch ($command) {
    case 'interactive':
        (new InteractiveInput($reader, $logger, $argsHandler, $hyphenator, $writer, $wordRepo))->process();
        break;
    case 'text':
        (new TextBlockInput($reader, $argsHandler, $hyphenator, $fileHandler, $wordRepo))->process();
        break;
    case 'batch':
        (new BatchProcess($reader, $hyphenator, $fileHandler))->process();
        break;
    case 'import':
        (new ImportData($argsHandler, $reader, $logger, $hyphenator, $patternRepo, $wtpRepo, $wordRepo))->process();
        break;
    default:
        throw new Exception(sprintf('Unknown command "%s"', $command));
}
