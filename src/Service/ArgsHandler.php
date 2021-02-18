<?php

namespace App\Service;

use Exception;

/**
 * Provides tools for managing cli arguments
 *
 * @package App\Service
 */
class ArgsHandler
{
    private App $app;
    
    private array $argsConfig; /* = [   // example config
        'input' => [
            'long' => 'input',
            'short' => 'i',
            'required' => true,         // optional, defaults to false
            'values' => [               // optional. If not set, accepts any value
                'val1', 'val2'
            ]
        ]
    ]*/
    private array $parsedArgs;
    
    public function __construct(App $app)
    {
        $this->argsConfig = [];
        $this->app = $app;
    }
    
    /**
     * @param  string        $long       Full argument key
     * @param  ?string       $short      Short version key
     * @param  bool          $isRequired If required argument isn't passed, will throw exception
     * @param  array<string> $values     Allowed values, optional (will accept any value then)
     * @return bool Was operation successful? Can be false if called in http environment
     */
    public function addArgConfig(string $long, string $short = null, bool $isRequired = false, array $values = []): bool
    {
        if (!$this->app->isCliEnv()) {
            return false;
        }
        
        foreach ($this->argsConfig as $singleConf) {
            if ($singleConf['long'] === $long || $singleConf['short'] === $short) {
                throw new Exception(sprintf('Argument key %s/%s already exists', $long, $short));
            }
        }
        
        $this->argsConfig[$long] = [
            'long' => $long,
            'short' => $short,
            'required' => $isRequired,
            'values' => $values
        ];
        
        $this->parseArgs();
        
        return true;
    }
    
    public function isSet(string $key): bool
    {
        return isset($this->parsedArgs[$key]);
    }
    
    public function get(string $key, ?string $default = null): string
    {
        if (!$this->isSet($key)) {
            if ($default !== null) {
                return $default;
            } else {
                throw new Exception(sprintf('Cannot get unset argument "%s"', $key));
            }
        }
        
        return $this->parsedArgs[$key];
    }
    
    /**
     * Parses args according to the current config
     */
    private function parseArgs(): void
    {
        $this->parsedArgs = [];
        $shortOptions = "";
        $longOptions = [];
    
        // build options string/array
        foreach ($this->argsConfig as $singleConf) {
            $shortOptions .= sprintf('%s:', $singleConf['short']);
            $longOptions[] = sprintf('%s:', $singleConf['long']);
        }
    
        $argsInput = getopt($shortOptions, $longOptions);
    
        // map passed args to $parsedArgs
        foreach ($this->argsConfig as $singleConf) {
            $short = $singleConf['short'];
            $long = $singleConf['long'];
        
            $value = null;
        
            if (isset($argsInput[$long])) {
                if (is_array($argsInput[$long])) {
                    throw new Exception('getopt is bugged again');
                }
                $value = $argsInput[$long];
            } else if (isset($argsInput[$short])) {
                if (is_array($argsInput[$short])) {
                    throw new Exception('getopt is bugged again');
                }
                $value = $argsInput[$short];
            }
    
            // unset and but required
            if ($value === null && $singleConf['required'] === true) { 
                throw new Exception(sprintf('Required parameter "%s" is not set', $long));
            }
    
            // value invalid
            if ($value !== null  
                && !empty($singleConf['values']) 
                && !in_array($value, $singleConf['values'])
            ) {
                throw new Exception(sprintf('Argument "%s" value "%s" is not allowed', $long, $value));
            }
            $this->parsedArgs[$long] = $value;
        }
    }
}
