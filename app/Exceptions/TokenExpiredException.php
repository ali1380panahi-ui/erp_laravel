<?php

namespace App\Exceptions;

use Exception;

class TokenExpiredException extends Exception
{
    protected $message = 'refresh_token_expired';
    protected $code = 401;
}