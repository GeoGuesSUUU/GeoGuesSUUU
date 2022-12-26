<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ItemTypeNotValidApiException extends ApiException
{
    public function __construct(string $message = "Item not valid", Exception $previous = null)
    {
        parent::__construct($message, Response::HTTP_BAD_REQUEST, $previous);
    }
}
