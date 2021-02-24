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
    public function echoResponse(Response $response)
    {
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }
        echo $response->getData();
    }
}
