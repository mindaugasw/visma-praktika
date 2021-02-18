<?php

namespace App\Service\Response;

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
}
