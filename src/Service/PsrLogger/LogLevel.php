<?php

namespace App\Service\PsrLogger;

/**
 * Describes log levels.
 */
class LogLevel
{
    /** @var string System is unusable */
    const EMERGENCY = 'emergency';
    /** @var string Action must be taken immediately */
    const ALERT     = 'alert';
    /** @var string Critical conditions */
    const CRITICAL  = 'critical';
    /** @var string Runtime errors that do not require immediate action but should typically
     * be logged and monitored */
    const ERROR     = 'error';
    /** @var string Exceptional occurrences that are not errors */
    const WARNING   = 'warning';
    /** @var string Normal but significant events */
    const NOTICE    = 'notice';
    /** @var string Interesting events */
    const INFO      = 'info';
    /** @var string Detailed debug information */
    const DEBUG     = 'debug';
    
    private static array $values = [
        self::EMERGENCY => 0,
        self::ALERT => 2,
        self::CRITICAL => 3,
        self::ERROR => 4,
        self::WARNING => 5,
        self::NOTICE => 6,
        self::INFO => 7,
        self::DEBUG => 8,
    ];
    
    /**
     * Should this message be logged, based on message level and current app logging level 
     * @param string $messageLevel
     * @param string $currentLevel
     * @return bool
     */
    public static function shouldLog(string $messageLevel, string $currentLevel): bool
    {
        return self::$values[$messageLevel] <= self::$values[$currentLevel];
    }
}