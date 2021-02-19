<?php

namespace App\Service;

use Exception;
use SplFileObject;

class FileHandler
{
    /**
     * Before opening file, create corresponding directory if it doesn't exist
     *
     * @param  string $filePath
     * @param  string $mode     File open mode
     * @return SplFileObject
     */
    public function openWithMkdir(string $filePath, string $mode): SplFileObject
    {
        $dirname = dirname($filePath);
        
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            throw new Exception(sprintf('Could not create log file directory "%s"', $dirname));
        }
     
        return new SplFileObject($filePath, $mode);
    }
}
