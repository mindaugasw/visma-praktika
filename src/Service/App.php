<?php

namespace App\Service;

use App\Command\ImportData;
use App\Command\InteractiveInput;
use App\Command\TextBlockInput;
use App\Repository\HyphenationPatternRepository;
use App\Repository\WordResultRepository;
use App\Repository\WordToPatternRepository;
use App\Service\DB\DBConnection;
use App\Service\DB\QueryBuilder;
use App\Service\Hyphenator\HyphenationHandler;
use App\Service\Hyphenator\Hyphenator;
use App\Service\PsrLogger\Logger;
use App\Service\Response\ResponseHandler;
use Exception;

class App
{
    public FileHandler $fileHandler;
    public Config $config;
    public Logger $logger;
    public ArgsHandler $argsHandler;
    public DBConnection $db;
    public QueryBuilder $queryBuilder;
    public HyphenationPatternRepository $patternRepo;
    public WordToPatternRepository $wtpRepo;
    public WordResultRepository $wordRepo;
    public InputReader $reader;
    public OutputWriter $writer;
    public Hyphenator $hyphenator;
    public HyphenationHandler $hyphenationHandler;
    public Router $router;
    public ResponseHandler $responseHandler;
    
    private bool $isCliEnv;
    
    public function __construct()
    {
        $this->isCliEnv = http_response_code() === false;
        
        $this->initializeServices();
        
        $this->argsHandler->addArgConfig('method', 'm', false, ['array', 'tree']);
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
    
    public function httpRoute(): void
    {
        $this->router->route();
    }
    
    private function initializeServices(): void
    {
        $this->fileHandler = new FileHandler();
        $this->config = new Config();
        $this->logger = new Logger($this, $this->fileHandler, $this->config);
        $this->argsHandler = new ArgsHandler($this);
        $this->db = new DBConnection($this->config, $this->logger);
        $this->queryBuilder = new QueryBuilder();
        $this->patternRepo = new HyphenationPatternRepository($this->db);
        $this->wtpRepo = new WordToPatternRepository($this->db);
        $this->wordRepo = new WordResultRepository($this->db, $this->wtpRepo, $this->logger);
        $this->reader = new InputReader($this->argsHandler, $this->logger, $this->patternRepo);
        $this->writer = new OutputWriter();
        $this->hyphenator = new Hyphenator();
        $this->hyphenationHandler = new HyphenationHandler($this->hyphenator, $this->wordRepo, $this->reader);
        $this->responseHandler = new ResponseHandler();
        $this->router = new Router($this, $this->responseHandler);
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
    
    /**
     * @return bool
     */
    public function isCliEnv(): bool
    {
        return $this->isCliEnv;
    }
}
