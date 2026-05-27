<?php

namespace App\Exceptions;

use Exception;

// thrown when PIN is invalid
class InvalidPinException extends Exception
{
    public function __construct($message = 'Invalid PIN', $code = 401)
    {
        parent::__construct($message, $code);
    }
}
