<?php

namespace App\Service;

use Exception;

class Profiler
{
    private const TIME_DIVISOR_MS = 1_000_000; // ns to ms
    private const TIME_DIVISOR_S = 1_000_000_000; // ns to s
    
    private static array $timers = []; // Start times
    private static array $partialTimes = []; // Partial times sum, used for pausing
    
    /**
     * Start timer with a given $key.
     * If $key is null, generates a random one.
     *
     * @param  null $key
     * @return string Timer $key
     */
    public static function start($key = null): string
    {
        if ($key === null) {
            $key = self::generateRandomString(4);
        }
        
        self::$timers[$key] = hrtime(true);
        return $key;
    }
    
    /**
     * Stop timer and get execution time, in selected $units
     *
     * @param  $key
     * @param  string $units ns|ms|s
     * @return float Execution time
     */
    public static function stop($key, string $units = 'ms'): float
    {
        $endTime = hrtime(true);
        
        if (!key_exists($key, self::$timers)) {
            throw new Exception(sprintf('Key "%s" does not exist', $key));
        }
        
        return ($endTime - self::$timers[$key]) / self::unitsToDivisor($units);
    }
    
    /**
     * Stop timer and get formatted string
     *
     * @param  $key
     * @param  string $units
     * @return string
     */
    public static function stopString($key, string $units = 'ms'): string
    {
        $value = self::stop($key, $units);
        return sprintf("Time @ %s: %f %s\n", $key, $value, $units);
    }
    
    /**
     * Stop timer and echo result
     *
     * @param  $key
     * @param  string $units
     * @return float
     * @throws Exception
     */
    public static function stopEcho($key, string $units = 'ms'): void
    {
        //$value = self::stop($key, $units);
        echo self::stopString($key, $units);
        //return $value;
    }
    
    /**
     * Pause timer. Timer should be started again before another pause.
     * Pausing multiple times adds together all execution times.
     *
     * @param  $key
     * @throws Exception
     */
    public static function pause($key): void
    {
        $endTime = hrtime(true);
        $totalTime = $endTime - self::$timers[$key];
        
        if (!key_exists($key, self::$partialTimes)) {
            self::$partialTimes[$key] = $totalTime;
        } else {
            self::$partialTimes[$key] += $totalTime;
        }
    }
    
    /**
     * Get combined execution time.
     * Timer should paused first with pause()
     *
     * @param  $key
     * @param  string $units
     * @return float
     * @throws Exception
     */
    public static function pausedGet($key, string $units = 'ms'): float
    {
        if (!key_exists($key, self::$partialTimes)) {
            throw new Exception(sprintf('Key "%s" does not exist', $key));
        }
        
        return self::$partialTimes[$key] / self::unitsToDivisor($units);
    }
    
    /**
     * Print combined execution time.
     * Timer should be paused first with pause()
     *
     * @param  $key
     * @param  string $units
     * @throws Exception
     */
    public static function pausedEcho($key, string $units = 'ms')
    {
        echo sprintf(
            "Combined time @ %s: %f %s\n",
            $key,
            self::pausedGet($key, $units), $units
        );
    }
    
    
    private static function unitsToDivisor(string $units): int
    {
        if ($units === "ns") {
            return 1;
        } else if ($units === "ms") {
            return self::TIME_DIVISOR_MS;
        } else if ($units === "s") {
            return self::TIME_DIVISOR_S;
        } else {
            throw new Exception("Unknown units \"$units\"");
        }
    }
    
    private static function generateRandomString($length = 10): string
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
    }
    
}