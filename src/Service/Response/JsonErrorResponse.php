<?php

namespace App\Service\Response;

class JsonErrorResponse extends JsonResponse
{
    private const ERROR_MESSAGES = [
        0 => 'Error occurred when processing request',
        400 => 'Bad request',
        404 => 'Item not found',
        500 => 'Server error occurred'
    ];
    
    public function __construct(?string $errorMsg = null, int $statusCode = 400, array $headers = [])
    {
        if (empty($errorMsg)) {
            if (isset(self::ERROR_MESSAGES[$statusCode])) {
                $errorMsg = self::ERROR_MESSAGES[$statusCode];
            } else {
                $errorMsg = self::ERROR_MESSAGES[0];
            }
        }
        
        $data = [
            'error' => $errorMsg,
            'status' => $statusCode
        ];
        
        parent::__construct($data, $statusCode, $headers);
    }
}
