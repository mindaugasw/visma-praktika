<?php
namespace App\Service\PsrLogger;

class Logger extends AbstractLogger
{
    //private const DATE_FORMAT = "Y-m-d";
    //private const TIME_FORMAT = "H:i:s";
    private const DATETIME_FORMAT = "Y-m-d H:i:s";
    
    private \SplFileObject $logFile;
    
    public function __construct($logFile = "log.txt")
    {
        $fullPath = __DIR__."/../../../var/log/".$logFile;
        $dirname = dirname($fullPath);
        
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true))
            throw new \Exception("Could not create log file directory \"$dirname\"");
            
        $this->logFile = new \SplFileObject($fullPath, "a");
    }
    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param mixed[] $context
     * @return void
     * @throws \InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        $this->logFile->fwrite(
            strtoupper($level)." @ "
            .date(self::DATETIME_FORMAT).": "
            .$this->interpolate($message, $context)."\n\n"
        );
    }
    
    /**
     * Interpolates context values into the message placeholders.
     * @param $message
     * @param array $context
     * @return string
     */
    private function interpolate($message, array $context = [])
    {
        // support {date}, {time}, {datetime} replacements, if they're not defined in $context 
        /*if (!isset($context["date"]))
            $context["date"] = date(self::DATE_FORMAT);
        if (!isset($context["time"]))
            $context["time"] = date(self::TIME_FORMAT);
        if (!isset($context["datetime"]))
            $context["datetime"] = date(self::DATETIME_FORMAT);*/
        
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
    }
}