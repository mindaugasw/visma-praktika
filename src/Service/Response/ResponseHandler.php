<?php

namespace App\Service\Response;

use App\Exception\NotFoundException;

class ResponseHandler
{
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
    
    public function returnFile(string $filename, bool $download = false)
    {
        if (!file_exists($filename)) {
            throw new NotFoundException(sprintf('File "%s" not found', $filename));
        }
        
        http_response_code(200);
        header(sprintf('Content-Type: %s', mime_content_type($filename)));
        header('Content-Transfer-Encoding: Binary');
        header(sprintf('Content-Length: %d', filesize($filename)));
        
        if ($download) {
            header(sprintf('Content-Disposition: attachment; filename=%s', $filename));
        }
        
        readfile($filename);
    }
}
