<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $original = $response->getOriginalContent();

        if (is_array($original)) {
            $statusCode = $original['statusCode'] ?? 200;

            if(isset($original['statusCode'])) unset($original['statusCode']);

            return response()->json($original, $statusCode);
        }

        return $response;
    }
}
