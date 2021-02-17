<?php

namespace App\Service;

use App\Command\CommandManager;
use App\Repository\HyphenationPatternRepository;
use App\Repository\WordResultRepository;
use App\Repository\WordToPatternRepository;
use App\Service\DB\DBConnection;
use App\Service\DB\QueryBuilder;
use App\Service\Hyphenator\HyphenationHandler;
use App\Service\Hyphenator\Hyphenator;
use App\Service\PsrLogger\Logger;
use App\Service\Response\ResponseHandler;

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
    public CommandManager $commandManager;
    
    private bool $isCliEnv;
    
    public function __construct()
    {
        $this->isCliEnv = http_response_code() === false;
        
        $this->initializeServices();
    }
    
    /**
     * Create all services
     */
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
        $this->commandManager = new CommandManager($this);
    }
    
    /**
     * @return bool
     */
    public function isCliEnv(): bool
    {
        return $this->isCliEnv;
    }
}
