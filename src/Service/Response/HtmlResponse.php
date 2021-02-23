<?php

declare(strict_types=1);

namespace App\Service\Response;

use App\Template\CommonUtils;
use Exception;

class HtmlResponse extends Response
{
    private const TEMPLATES_DIR = __DIR__ . '/../../Template/';
    
    public function __construct(string $template, array $templateArgs = [], int $statusCode = 200, array $headers = [])
    {
        $filename = self::TEMPLATES_DIR . $template . '.tpl.php';
        
        $GLOBALS['tpl'] = $templateArgs;
        $content = CommonUtils::includeString($filename);
        
        parent::__construct($content, $statusCode, $headers);
    }
    
    public function setData(string $data): void
    {
        throw new Exception('Setting data not allow on HtmlResponse. Use constructor.');
    }
    
    /**
     * Returns template content as string, instead of outputting straight to
     * stdout with include
     * @param string $filename
     * @return string
     */
    private function getTemplateContent(string $filename): string
    {
        ob_start();
        include $filename;
        $content = ob_get_clean();
        ob_end_clean();
        return $content;
    }
}
