<?php

namespace App\Service\Response;

class Response
{
    // headers keys
    protected const HEADER_CONTENT_TYPE = 'Content-Type';
    
    protected string $data;
    protected int $statusCode;
    protected array $headers;
    
    /**
     * Response constructor.
     *
     * @param string $data
     * @param int    $statusCode
     * @param array  $headers    assoc array of headers. headerKey => headerValue
     */
    public function __construct(string $data, int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }
    
    public function addHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }
    
    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }
    
    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
