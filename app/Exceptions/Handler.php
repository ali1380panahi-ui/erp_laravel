<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as BaseHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidRefreshTokenException;
use App\Exceptions\TokenAlreadyUsedException;
use App\Exceptions\TokenExpiredException;
use Laravel\Sanctum\PersonalAccessToken;

class Handler extends BaseHandler
{
    public function register(): void
    {

        $this->handleValidationException();

        $this->handleAuthException();

        $this->handleDbException();

        $this->handleOtherException();
    }

    private function handleValidationException()
    {
        $this->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'message' => 'validation_error',
                'errors' => $this->formatValidationErrors($e),
                'statusCode' => 422
            ] , 422);
        });
    }

    private function formatValidationErrors(ValidationException $e): array
    {
        $errors = [];

        $failedRules = $e->validator->failed();

        foreach ($failedRules as $field => $rules) {

            foreach ($rules as $rule => $params) {

                $key = 'validation.' . strtolower($rule);

                $errors[] = [
                    'field' => $field,
                    'message' => db_trans($key)
                ];
            }
        }

        return $errors;
    }


    private function handleAuthException()
    {

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                $token = $request->bearerToken();
                $isExpired = false;

                if ($token) {
                    $accessToken = PersonalAccessToken::findToken($token);
                    if ($accessToken && $accessToken->expires_at && $accessToken->expires_at->isPast()) {
                        $isExpired = true;
                    }
                }

                $message = $isExpired ? 'access_token_expired' : 'invalide_access_token';

                return response()->json([
                    'message' => $message,
                    'statusCode' => 401
                ], 401);
            }
        });


        $authExceptions = [
            InvalidCredentialsException::class,
            InvalidRefreshTokenException::class,
            TokenAlreadyUsedException::class,
            TokenExpiredException::class,
        ];

        $this->renderable(function (InvalidCredentialsException $e, $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'translated_message' => db_trans('alerts.' . $e->getMessage()),
                'statusCode' => $e->getCode()
            ], $e->getCode());
        });


        $this->renderable(function (InvalidRefreshTokenException $e, $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'translated_message' => db_trans('alerts.' . $e->getMessage()),
                'statusCode' => $e->getCode()
            ], $e->getCode());
        });


        $this->renderable(function (TokenAlreadyUsedException $e, $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'translated_message' => db_trans('alerts.' . $e->getMessage()),
                'statusCode' => $e->getCode()
            ], $e->getCode());
        });


        $this->renderable(function (TokenExpiredException $e, $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'translated_message' => db_trans('alerts.' . $e->getMessage()),
                'statusCode' => $e->getCode()
            ], $e->getCode());
        });


    }

    private function handleDbException()
    {
        $this->renderable(function (QueryException $e, $request) {
            return response()->json([
                'message' => 'server_error',
                'translated_message' => db_trans('alerts.server_error1'),
                'statusCode' => 500,
                'e' => $e
            ], 500);
        });
    }

    private function handleOtherException()
    {
        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                
                $message = config('app.debug') ? $e->getMessage() : 'server_error';
                $translated_message = db_trans($message);

                return response()->json([
                    'message' => 'server_error',
                    'translated_message' => db_trans('alerts.server_error'),
                    'statusCode' => 500
                ], 500);
            }
        });
    }
}
