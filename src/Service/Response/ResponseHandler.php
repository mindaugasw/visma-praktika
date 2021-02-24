<?php

namespace App\Service\Response;

use App\Exception\NotFoundException;

class ResponseHandler
{
    /**
     * Replace mime types for files with $key extension with $value mime type
     */
    private const MIME_REPLACE = [
        'js' => 'text/javascript',
        'css' => 'text/css',
    ];
    
    public function __construct()
    {
    }
    
    /**
     * Send response to the client
     *
     * @param Response $response
     */
    public function returnResponse(Response $response)
    {
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }
        echo $response->getData();
    }
    
    /**
     * Output a file to the stdout
     * @param string $filename
     * @param bool $download
     */
    public function returnFile(string $filename, bool $download = false)
    {
        // TODO move to a separate Response class
        if (!file_exists($filename)) {
            throw new NotFoundException(sprintf('File "%s" not found', $filename));
        }
        
        // mime_content_type() doesn't always correctly return js type
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (isset(self::MIME_REPLACE[$extension])) {
            $mimeType = self::MIME_REPLACE[$extension];
        } else {
            $mimeType = mime_content_type($filename);
        }
        
        http_response_code(200);
        header(sprintf('Content-Type: %s', $mimeType));
        header('Content-Transfer-Encoding: Binary');
        header(sprintf('Content-Length: %d', filesize($filename)));
        
        if ($download) {
            header(sprintf('Content-Disposition: attachment; filename=%s', $filename));
        }
        
        readfile($filename);
    }
}
