<?php

namespace App\Command;

use App\Service\ArgsHandler;
use App\Service\DIContainer\Container;
use Exception;

class CommandManager
{
    private Container $diContainer;
    private ArgsHandler $argsHandler;
    
    public function __construct(Container $diContainer, ArgsHandler $argsHandler)
    {
        $this->diContainer = $diContainer;
        $this->argsHandler = $argsHandler;
    }
    
    /**
     * Automatically choose command based on cli args and execute it
     */
    public function autoExecuteCommand(): void
    {
        $this->argsHandler->addArgConfig('method', 'm', false, ['array', 'tree']);
        $this->argsHandler->addArgConfig('command', 'c', true);
        
        $command = $this->argsHandler->get('command');
        $command = sprintf('command%s', ucfirst(strtolower($command)));
    
        if (!is_callable([$this, $command])) {
            throw new Exception(sprintf('Command "%s" not found', $command));
        }
    
        $this->$command();
    }
    
    public function commandInteractive(): void
    {
        ($this->diContainer->get(InteractiveInput::class))->process();
    }
    
    public function commandText(): void
    {
        ($this->diContainer->get(TextBlockInput::class))->process();
    }
    
    public function commandImport(): void
    {
        ($this->diContainer->get(ImportData::class))->process();
    }
}
