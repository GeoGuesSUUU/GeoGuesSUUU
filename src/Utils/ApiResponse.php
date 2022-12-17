<?php

namespace App\Utils;


use App\Exception\BadRequestApiException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiResponse
{

    /**
     * @throws HttpException
     */
    public static function get(mixed $value, int $httpStatus = 200): array
    {
        if (!isset($value) && $httpStatus !== 204) {
            throw new BadRequestApiException();
        }
        $response = [
            'response' => [
                'status' => $httpStatus,
                'request' => $_SERVER["REQUEST_URI"],
                'method' => $_SERVER["REQUEST_METHOD"],
            ],
            'items' => $value ?? "",
        ];
        if (is_array($value)) {
            $response['response']['total'] = count($value);
        }
        return $response;
    }
}
