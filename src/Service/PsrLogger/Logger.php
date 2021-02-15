<?php
namespace App\Service\PsrLogger;

use App\Service\App;
use App\Service\Config;
use App\Service\FileHandler;

class Logger extends AbstractLogger
{
    // Supported config
    const CFG_LEVEL_CONSOLE = ['key' => 'log_level_console', 'default' => LogLevel::INFO];
    const CFG_LEVEL_FILE = ['key' => 'log_level_file', 'default' => LogLevel::ALERT];
    const CFG_LOG_FILE = ['key' => 'log_file', 'default' => '/var/log/log.txt'];
    
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    
    private App $app;
    private Config $config;
    private FileHandler $fileHandler;
    
    private \SplFileObject $logFile;
    private string $consoleLevel;
    private string $fileLevel;
    
    public function __construct(App $app, FileHandler $fileHandler, Config $config)
    {
        $this->app = $app;
        $this->fileHandler = $fileHandler;
        $this->config = $config;
        
        $this->consoleLevel = $config->get(self::CFG_LEVEL_CONSOLE['key'], self::CFG_LEVEL_CONSOLE['default']);
        $this->fileLevel = $config->get(self::CFG_LEVEL_FILE['key'], self::CFG_LEVEL_FILE['default']);
        $logFilePath = $config->get(self::CFG_LOG_FILE['key'], self::CFG_LOG_FILE['default']);
        
        $fullPath = sprintf('%s/../../..%s', __DIR__, $logFilePath);
        $this->logFile = $fileHandler->openWithMkdir($fullPath, 'a');
    }
    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message log message. Can be vsprintf format string
     * @param array $context Args for vsprintf
     * @return void
     * @throws \InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        // log to console
        if ($this->app->isCliEnv() && LogLevel::shouldLog($level, $this->consoleLevel)) {
            echo vsprintf($message."\n", $context);
        }
        
        // log to file
        if (LogLevel::shouldLog($level, $this->fileLevel)) {
            $this->logFile->fwrite(
                sprintf(
                    "%s @ %s: %s\n\n",
                    strtoupper($level),
                    date(self::DATETIME_FORMAT),
                    vsprintf((string)$message, $context)
                )
            );
        }
    }
    
    /*
     * Interpolates context values into the message placeholders.
     * @param $message
     * @param array $context
     * @return string
     *
    private function interpolate($message, array $context = [])
    {
        // support {date}, {time}, {datetime} replacements, if they're not defined in $context 
        /*if (!isset($context["date"]))
            $context["date"] = date(self::DATE_FORMAT);
        if (!isset($context["time"]))
            $context["time"] = date(self::TIME_FORMAT);
        if (!isset($context["datetime"]))
            $context["datetime"] = date(self::DATETIME_FORMAT);*
        
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be cast to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        
        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }*/
}