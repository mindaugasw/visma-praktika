<?php

declare(strict_types=1);

namespace App\Service\Response;

use App\Exception\NotFoundException;

/**
 * Returns a static asset
 */
class FileResponse extends Response
{
    /**
     * Replace mime types cuz mime_content_type() doesn't work reliably
     * Sets $value mime type for files with $key extension
     */
    private const MIME_REPLACE = [
        'js' => 'text/javascript',
        'css' => 'text/css',
    ];
    
    /**
     * @param string $filename
     * @param bool $download False - view file in browser, True - download file to disk
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct(string $filename, bool $download = false, int $statusCode = 200, array $headers = [])
    {
        if (!file_exists($filename)) {
            throw new NotFoundException(sprintf('File "%s" not found', $filename));
        }
    
        // mime_content_type() doesn't always correctly guess type
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (isset(self::MIME_REPLACE[$extension])) {
            $mimeType = self::MIME_REPLACE[$extension];
        } else {
            $mimeType = mime_content_type($filename);
        }
        
        $headers['Content-Type'] = $mimeType;
        $headers['Content-Transfer-Encoding'] = 'Binary';
        $headers['Content-Length'] = filesize($filename);
    
        if ($download) {
            $headers['Content-Disposition'] = 'attachment; filename=' . $filename;
        }
        
        $data = $this->getFileContent($filename);
        
        parent::__construct($data, $statusCode, $headers);
    }
    
    public function getFileContent(string $filename): string
    {
        ob_start();
        readfile($filename);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}
