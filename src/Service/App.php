<?php

namespace App\Service;

use App\Command\ImportData;
use App\Command\InteractiveInput;
use App\Command\TextBlockInput;
use App\Repository\HyphenationPatternRepository;
use App\Repository\WordResultRepository;
use App\Repository\WordToPatternRepository;
use App\Service\PsrLogger\Logger;
use Exception;

class App
{
    private FileHandler $fileHandler;
    private Config $config;
    private Logger $logger;
    private ArgsHandler $argsHandler;
    private DBConnection $db;
    private HyphenationPatternRepository $patternRepo;
    private WordToPatternRepository $wtpRepo;
    private WordResultRepository $wordRepo;
    private InputReader $reader;
    private OutputWriter $writer;
    private Hyphenator $hyphenator;
    
    /** @var array CLI command and args */
    private array $argv;
    
    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->initializeServices();
        $this->logger->debug('Starting application, "%s"', [implode(' ', $argv)]);
    
        $this->argsHandler->addArgConfig('method', 'm', false, ['array', 'tree']);
    }
    
    private function initializeServices(): void
    {
        $this->fileHandler = new FileHandler();
        $this->config = new Config();
        $this->logger = new Logger($this->fileHandler, $this->config);
        $this->argsHandler = new ArgsHandler();
        $this->db = new DBConnection($this->config, $this->logger);
        $this->patternRepo = new HyphenationPatternRepository($this->db);
        $this->wtpRepo = new WordToPatternRepository($this->db);
        $this->wordRepo = new WordResultRepository($this->db, $this->wtpRepo, $this->logger);
        $this->reader = new InputReader($this->argsHandler, $this->logger, $this->patternRepo);
        $this->writer = new OutputWriter();
        $this->hyphenator = new Hyphenator();
    }
    
    public function autoChooseCommand(): void
    {
        $this->argsHandler->addArgConfig('command', 'c', true);
        $command = $this->argsHandler->get('command');
        $command = sprintf('command%s', ucfirst(strtolower($command)));
        
        if (!is_callable([$this, $command]))
            throw new Exception(sprintf('Command "%s" not found', $command));
        
        $this->$command();
    }
    
    public function commandInteractive(): void
    {
        (new InteractiveInput(
            $this->reader,
            $this->logger,
            $this->argsHandler,
            $this->hyphenator,
            $this->writer,
            $this->wordRepo
        ))->process();
    }
    
    public function commandText(): void
    {
        (new TextBlockInput(
            $this->reader,
            $this->argsHandler,
            $this->hyphenator,
            $this->fileHandler,
            $this->wordRepo
        ))->process();
    }
    
    public function commandImport(): void
    {
        (new ImportData(
            $this->argsHandler,
            $this->reader,
            $this->logger,
            $this->hyphenator,
            $this->patternRepo,
            $this->wtpRepo,
            $this->wordRepo
        ))->process();
    }
}
