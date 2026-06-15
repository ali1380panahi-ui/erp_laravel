<?php

namespace App\Exceptions;

use Exception;

class TokenAlreadyUsedException extends Exception
{
    protected $message = 'toknen_used_before';
    protected $code = 401;
}