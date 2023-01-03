<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserForbiddenAccessApiException extends ApiException
{
    public function __construct(Exception $previous = null)
    {
        parent::__construct("Forbidden Access", Response::HTTP_FORBIDDEN, $previous);
    }
}
