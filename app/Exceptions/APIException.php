<?php

namespace App\Exceptions;

use Exception;

class APIException extends Exception
{
    public function __construct($message = "", $code = 400)
    {
        parent::__construct($message, $code);
    }
}