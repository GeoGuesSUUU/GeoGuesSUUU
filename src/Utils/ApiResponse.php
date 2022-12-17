<?php

namespace App\Utils;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{

    /**
     * @throws Exception
     */
    static function get(mixed $value, int $httpStatus = 400): array
    {
        if (!isset($value)) {
            return [
                'response' => [
                    'request' => $_SERVER["REQUEST_URI"],
                    'method' => $_SERVER["REQUEST_METHOD"],
                ],
                'error' => self::getHttpError($httpStatus),
            ];
        }
        $response = [
            'response' => [
                'request' => $_SERVER["REQUEST_URI"],
                'method' => $_SERVER["REQUEST_METHOD"],
            ],
            'items' => $value,
        ];
        if (is_array($value)) $response['response']['total'] = count($value);
        return $response;
    }

    /**
     * @throws Exception
     */
    static function getHttpError(int $httpStatus): array
    {
        $message = Response::$statusTexts[$httpStatus];
        if (empty($message)) throw new Exception("Http Status ".$httpStatus." doesn't exist");
        return [
            'code' => $httpStatus,
            'message' => $message,
        ];
    }
}