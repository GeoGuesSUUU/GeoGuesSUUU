<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ScoreNotFoundApiException extends ApiException
{
    public function __construct(Exception $previous = null)
    {
        parent::__construct("Score not found", Response::HTTP_NOT_FOUND, $previous);
    }
}
