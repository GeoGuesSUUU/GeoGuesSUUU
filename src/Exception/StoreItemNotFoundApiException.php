<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class StoreItemNotFoundApiException extends ApiException
{
    public function __construct(Exception $previous = null)
    {
        parent::__construct("Store Item not found", Response::HTTP_NOT_FOUND, $previous);
    }
}
