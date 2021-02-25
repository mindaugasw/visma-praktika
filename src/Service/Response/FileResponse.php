<?php

declare(strict_types=1);

namespace App\Service\Response;

use App\Exception\NotFoundException;

/**
 * Returns a static asset
 */
class FileResponse extends Response
{
    protected const HEADER_CONTENT_TRANSFER_ENCODING = 'Content-Transfer-Encoding';
    protected const HEADER_CONTENT_LENGTH = 'Content-Length';
    protected const HEADER_CONTENT_DISPOSITION = 'Content-Disposition';
    
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
        
        $headers[self::HEADER_CONTENT_TYPE] = $mimeType;
        $headers[self::HEADER_CONTENT_TRANSFER_ENCODING] = 'Binary';
        $headers[self::HEADER_CONTENT_LENGTH] = filesize($filename);
    
        if ($download) {
            $headers[self::HEADER_CONTENT_DISPOSITION] = 'attachment; filename=' . $filename;
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
