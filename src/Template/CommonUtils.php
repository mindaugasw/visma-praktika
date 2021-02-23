<?php

declare(strict_types=1);

namespace App\Template;

use App\Service\App;
use App\Service\DIContainer\Container;

class CommonUtils
{
    public static function includeBase()
    {
        include __DIR__ . '/base.tpl.php';
    }
    
    /**
     * Include file as string instead of directly outputting it.
     * @param string $fileName
     * @return string
     */
    public static function includeString(string $fileName): string
    {
        ob_start();
        include $fileName;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    
    /**
     * Set default templates to global $tpl array. Only sets those templates
     * which aren't already set
     */
    public static function setDefaultTemplates(): void
    {
        global $tpl;
    
        if (!array_key_exists('title', $tpl)) {
            $tpl['title'] = 'Hyphenator page';
        }
        
        if (!array_key_exists('stylesheets', $tpl)) {
            $tpl['stylesheets'] = <<<TPL
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
            TPL;
        }
    
        if (!array_key_exists('scripts', $tpl)) {
            // Popper.js:
            // <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js" integrity="sha384-KsvD1yqQ1/1+IA7gi3P0tyJcT3vR+NdBTt13hSJ2lnve8agRGXTTyNaBYmCR/Nwi" crossorigin="anonymous"></script>
            // jQuery:
            // <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
            
            $tpl['scripts'] = <<<TPL
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
            TPL;
        }
    
        if (!array_key_exists('navbar', $tpl)) {
            $tpl['navbar'] = self::includeString(__DIR__ . '/Common/navbar.tpl.php');
        }
    }
}
