<?php

declare(strict_types=1);

namespace App\Template;

class CommonUtils
{
    /**
     * Include base template and start rendering
     */
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
     * Get loading spinner template as string
     * @return string
     */
    public static function getLoadingSpinner(): string
    {
        return self::includeString(__DIR__ . '/../Template/Common/loading.tpl.php');
    }
    
    /**
     * Set default templates to global $tpl array. Only sets those templates
     * which aren't already set
     *
     * Template keys checked:
     * title - tab title
     * stylesheets - custom stylesheets for this page
     * stylesheetsAll - all stylesheets, includes 'stylesheets'
     * scripts - custom scripts for this page
     * scriptsAll - all scripts, includes 'scripts'
     * navbar - navbar template
     */
    public static function setDefaultTemplates(): void
    {
        global $tpl;
    
        // tab title
        if (!array_key_exists('title', $tpl)) {
            $tpl['title'] = 'Hyphenator page';
        }
    
        // custom stylesheets for this page
        if (!array_key_exists('stylesheets', $tpl)) {
            $tpl['stylesheets'] = '';
        }
        
        // all stylesheets, includes 'stylesheets'
        if (!array_key_exists('stylesheetsAll', $tpl)) {
            $tpl['stylesheetsAll'] = <<<TPL
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
                <link href="/css/style.css" rel="stylesheet"/>
                {$tpl['stylesheets']}
            TPL;
        }
    
        // custom scripts for this page
        if (!array_key_exists('scripts', $tpl)) {
            $tpl['scripts'] = '';
        }
        
        // all scripts, includes 'scripts'
        if (!array_key_exists('scriptsAll', $tpl)) {
            $tpl['scriptsAll'] = <<<TPL
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
                {$tpl['scripts']}
            TPL;
        }
    
        // navbar template
        if (!array_key_exists('navbar', $tpl)) {
            $tpl['navbar'] = self::includeString(__DIR__ . '/Common/navbar.tpl.php');
        }
    }
}
