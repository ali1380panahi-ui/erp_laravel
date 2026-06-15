<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\SessionManager;
use App\Models\User;
use App\Models\RefreshToken;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidRefreshTokenException;
use App\Exceptions\SessionRevokedException;
use App\trait\HandlesModelTransactions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function login(Request $request)
    {
        return $this->withTransaction(function () use ($request) {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw new InvalidCredentialsException();
            }


            $session = $this->sessionManager->createSession($user, $request);

            return [
                'message' => 'success',
                'data' => [
                    'access_token' => $session['access_token'],
                    'refresh_token' => $session['refresh_token'],
                    'session_id' => $session['session_id'],
                    'token_type' => 'Bearer'
                ]
            ];
        });
    }

    public function refresh(Request $request)
    {
        return $this->withTransaction(function () use ($request) {
            $request->validate([
                'refresh_token' => 'required|string'
            ]);

            $hashed = hash('sha256', $request->refresh_token);

            $token = RefreshToken::where('token', $hashed)
                ->where('status', RefreshToken::STATUS_ACTIVE)
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->first();

            if (!$token) {
                throw new InvalidRefreshTokenException();
            }

            if (!$this->sessionManager->validateSession($token->session_id, $token->user)) {
                throw new SessionRevokedException();
            }

            $token->update([
                'status' => RefreshToken::STATUS_USED,
                'used' => true,
                'used_at' => now()
            ]);


            $deviceInfo = [
                'device_name' => $token->device_name,
                'browser' => $token->browser,
                'ip_address' => $request->ip()
            ];

            $accessToken = $this->createAccessTokenForSession($token->user, $token->session_id, $deviceInfo);
            $newRefreshToken = RefreshToken::createForUser($token->user, $token->session_id, $deviceInfo)['refreshToken'];

            return [
                'message' => 'success',
                'data' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $newRefreshToken,
                    'token_type' => 'Bearer'
                ]
            ];
        });
    }

    public function logout(Request $request)
    {
        return $this->withTransaction(function () use ($request) {
            $user = $request->user();
            $token = $user->currentAccessToken();

            if ($token && $token->session_id) {
                $this->sessionManager->revokeSession($token->session_id, $user);
            }
            
            return [
                'message' => 'success'
            ];
        });
    }
    
    public function logoutAllDevices(Request $request)
    {
        return $this->withTransaction(function () use ($request) {
            $user = $request->user();

            $user->tokens()->update([
                'revoked_at' => now(),
                'revoked_reason' => 'logout_all_devices'
            ]);
            
            RefreshToken::where('user_id', $user->id)
                ->where('status', RefreshToken::STATUS_ACTIVE)
                ->update([
                    'status' => RefreshToken::STATUS_REVOKED,
                    'revoked_at' => now()
                ]);
            
            return [
                'message' => 'logout_all_devices_successful',
                'data' => []
            ];
        });
    }

    public function listSessions(Request $request)
    {
        $sessions = RefreshToken::where('user_id', $request->user()->id)
            ->where('status', RefreshToken::STATUS_ACTIVE)
            ->select('session_id', 'device_name', 'browser', 'ip_address', 'created_at', 'expires_at')
            ->get();

        return [
            'message' => 'success',
            'data' => $sessions
        ];
    }
    
    private function createAccessTokenForSession($user, string $sessionId, array $deviceInfo): string
    {
        $token = $user->createToken('access-token');
        
        $token->accessToken->update([
            'session_id' => $sessionId,
            'device_name' => $deviceInfo['device_name'],
            'browser' => $deviceInfo['browser'],
            'ip_address' => $deviceInfo['ip_address'],
        ]);
        
        return $token->plainTextToken;
    }
}