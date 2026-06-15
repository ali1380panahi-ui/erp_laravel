<?php

namespace App\Exceptions;

use Exception;

class InvalidRefreshTokenException extends Exception
{
    protected $message = 'invalid_refresh_token';
    protected $code = 401;
}