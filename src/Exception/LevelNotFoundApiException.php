<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class LevelNotFoundApiException extends ApiException
{
    public function __construct(Exception $previous = null)
    {
        parent::__construct("Level not found", Response::HTTP_NOT_FOUND, $previous);
    }
}
