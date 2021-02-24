<?php

declare(strict_types=1);

namespace App\Service\Response;

class ErrorResponse extends Response
{
    private const ERROR_MESSAGES = [
        0 => 'Error occurred when processing request',
        400 => 'Bad request',
        404 => 'Item not found',
        500 => 'Server error occurred'
    ];
    
    public function __construct(
        ?string $errorMsg = null,
        ?string $type = null,
        int $statusCode = 400,
        array $headers = []
    ) {
        if (empty($errorMsg)) {
            if (isset(self::ERROR_MESSAGES[$statusCode])) {
                $errorMsg = self::ERROR_MESSAGES[$statusCode];
            } else {
                $errorMsg = self::ERROR_MESSAGES[0];
            }
        }
        
        if ($type === null) {
            $type = '';
        }
        
        $responseData = [
            'error' => [
                'type' => $type,
                'message' => $errorMsg,
                'status' => $statusCode
            ]
        ];
        
        // determine whether to send html or json response
        // TODO move this logic to somewhere more fitting
        
        // if can accept html or accept header not set, send html
        if (
            !isset($_SERVER['HTTP_ACCEPT'])
            || str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'text/html')
        ) {
            // TODO refactor this to not have to create additional response
            $newResponse = new HtmlResponse(
                'Page/error',
                $responseData,
                $statusCode,
                $headers
            );
        } else {
            // ... otherwise send json
            $newResponse = new JsonResponse(
                $responseData,
                $statusCode,
                $headers
            );
        }
        
        parent::__construct(
            $newResponse->getData(),
            $newResponse->getStatusCode(),
            $newResponse->getHeaders()
        );
    }
}
