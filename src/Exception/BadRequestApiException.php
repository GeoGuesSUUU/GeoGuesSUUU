<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class BadRequestApiException extends ApiException
{
    public function __construct(Exception $previous = null)
    {
        parent::__construct("Bad Request", Response::HTTP_BAD_REQUEST, $previous);
    }
}
