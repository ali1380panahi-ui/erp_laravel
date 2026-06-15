<?php

namespace App\Exceptions;

use Exception;

class SessionRevokedException extends Exception
{
    protected $message = 'session_revoked';
    protected $code = 401;
}