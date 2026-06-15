<?php

namespace App\Services;

use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class SessionManager
{

    public function createSession(User $user, Request $request): array
    {
        $sessionId = $this->generateSessionId();
        $deviceInfo = $this->getDeviceInfo($request);

        $this->revokePreviousSessions($user);

        $accessToken = $this->createAccessToken($user, $sessionId, $deviceInfo);
        $refreshToken = $this->createRefreshToken($user, $sessionId, $deviceInfo);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'session_id' => $sessionId,
        ];
    }

    private function revokePreviousSessions(User $user): void
    {

        $user->tokens()->whereNull('revoked_at')->update([
            'revoked_at' => now(),
            'revoked_reason' => 'new_login_from_another_device'
        ]);

        RefreshToken::where('user_id', $user->id)
            ->where('status', RefreshToken::STATUS_ACTIVE)
            ->update([
                'status' => RefreshToken::STATUS_REVOKED,
                'revoked_at' => now(),
                'revoked_reason' => 'new_login_from_another_device'
            ]);
    }

    private function createAccessToken(User $user, string $sessionId, array $deviceInfo): string
    {
        $token = $user->createToken('access-token', ['*']);

        $realTokenId = DB::getPdo()->lastInsertId();

        PersonalAccessToken::where('id', $realTokenId)->update([
            'session_id'   => $sessionId,
            'device_name'  => $deviceInfo['device_name'],
            'browser'      => $deviceInfo['browser'],
            'ip_address'   => $deviceInfo['ip_address'],
        ]);


        return $token->plainTextToken;
    }

    private function createRefreshToken(User $user, string $sessionId, array $deviceInfo): string
    {
        $createToken = RefreshToken::createForUser($user, $sessionId, $deviceInfo);

        return $createToken['refreshToken'];
    }

    public function validateSession(string $sessionId, User $user): bool
    {
        $activeSession = RefreshToken::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->where('status', RefreshToken::STATUS_ACTIVE)
            ->exists();

        return $activeSession;
    }

    public function revokeSession(string $sessionId, User $user): void
    {
        // باطل کردن اکسس توکن
        $user->tokens()->where('session_id', $sessionId)->update([
            'revoked_at' => now(),
            'revoked_reason' => 'logout'
        ]);

        // باطل کردن رفرش توکن
        RefreshToken::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->update([
                'status' => RefreshToken::STATUS_REVOKED,
                'revoked_at' => now()
            ]);
    }
    
    
    private function getDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent();
        
        return [
            'device_name' => $this->parseDeviceName($userAgent),
            'browser' => $this->parseBrowser($userAgent),
            'ip_address' => $request->ip(),
        ];
    }
    
    private function generateSessionId(): string
    {
        return Str::uuid()->toString();
    }
    
    private function parseDeviceName(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows PC';
        if (str_contains($userAgent, 'Mac')) return 'Mac';
        if (str_contains($userAgent, 'iPhone')) return 'iPhone';
        if (str_contains($userAgent, 'Android')) return 'Android Device';
        return 'Unknown Device';
    }
    
    private function parseBrowser(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        return 'Unknown Browser';
    }
}