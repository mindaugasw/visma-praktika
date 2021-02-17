<?php


namespace App\Service\Response;


class JsonResponse extends Response
{
    /**
     * JsonResponse constructor.
     * @param mixed $data Will be JSON encoded
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct(mixed $data, int $statusCode = 200, array $headers = [])
    {
        parent::__construct(json_encode($data), $statusCode, $headers);
        
        if (!isset($this->headers[self::HEADER_CONTENT_TYPE])) {
            $this->addHeader(self::HEADER_CONTENT_TYPE, 'application/json');
        }
    }
}
