<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ItemFantasticAlreadyExistApiException extends ApiException
{
    public function __construct(Exception $previous = null)
    {
        parent::__construct("This fantastic item is already in user inventory", Response::HTTP_NOT_FOUND, $previous);
    }
}
