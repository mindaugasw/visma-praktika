<?php

namespace App\Service;

class ResponseCreator
{
    public function __construct()
    {
    }
    
    public function genericResponse($data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        foreach ($headers as $header)
            header($header);
        echo $data;
    }
    
    public function json($data, int $status = 200, array $headers = []): void
    {
        $this->genericResponse(json_encode($data), $status, $headers);
    }
}
