<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\SessionManager;
use App\Exceptions\SessionRevokedException;

class CheckActiveSession
{
    protected $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        
        if ($token && $token->session_id) {
            if (!$this->sessionManager->validateSession($token->session_id, $user)) {
                throw new SessionRevokedException();
            }
        }
        
        return $next($request);
    }
}