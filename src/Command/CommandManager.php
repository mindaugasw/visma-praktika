<?php


namespace App\Command;


use App\Service\App;
use Exception;

class CommandManager
{
    private App $app;
    
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    /**
     * Automatically choose command based on cli args and execute it
     */
    public function autoExecuteCommand(): void
    {
        if ($this->app->isCliEnv()) {
            $this->app->argsHandler->addArgConfig('method', 'm', false, ['array', 'tree']);
        }
        
        $this->app->argsHandler->addArgConfig('command', 'c', true);
        $command = $this->app->argsHandler->get('command');
        $command = sprintf('command%s', ucfirst(strtolower($command)));
    
        if (!is_callable([$this, $command])) {
            throw new Exception(sprintf('Command "%s" not found', $command));
        }
    
        $this->$command();
    }
    
    public function commandInteractive(): void
    {
        (new InteractiveInput(
            $this->app->reader,
            $this->app->logger,
            $this->app->argsHandler,
            $this->app->hyphenator,
            $this->app->writer,
            $this->app->wordRepo
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