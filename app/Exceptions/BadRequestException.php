<?php

namespace App\Exceptions;

use Exception;

class BadRequestException extends Exception
{
    public function __construct($message = "", $code = 400)
    {
        parent::__construct($message, $code);
    }
}