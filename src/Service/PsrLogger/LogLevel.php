<?php

namespace App\Service\PsrLogger;

/**
 * Describes log levels.
 */
class LogLevel extends \Psr\Log\LogLevel
{
    /**
     * @var string System is unusable
     */
    public const EMERGENCY = 'emergency';
    /**
     * @var string Action must be taken immediately
     */
    public const ALERT = 'alert';
    /**
     * @var string Critical conditions
     */
    public const CRITICAL = 'critical';
    /**
     * @var string Runtime errors that do not require immediate action but should typically
     * be logged and monitored
     */
    public const ERROR = 'error';
    /**
     * @var string Exceptional occurrences that are not errors
     */
    public const WARNING = 'warning';
    /**
     * @var string Normal but significant events
     */
    public const NOTICE = 'notice';
    /**
     * @var string Interesting events
     */
    public const INFO = 'info';
    /**
     * @var string Detailed debug information
     */
    public const DEBUG = 'debug';
    
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
     *
     * @param string $messageLevel
     * @param string $currentLevel
     * @return bool
     */
    public static function shouldLog(string $messageLevel, string $currentLevel): bool
    {
        return self::$values[$messageLevel] <= self::$values[$currentLevel];
    }
}
