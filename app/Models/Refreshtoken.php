<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RefreshToken extends Model
{
    protected $fillable = [
        'user_id', 
        'session_id', 
        'token', 
        'expires_at', 
        'status', 
        'used', 
        'used_at', 
        'device_name', 
        'browser', 
        'ip_address', 
        'revoked_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'revoked_at' => 'datetime',
        'used' => 'boolean',
    ];


    const STATUS_ACTIVE = 'active';
    const STATUS_USED = 'used';
    const STATUS_REVOKED = 'revoked';
    const STATUS_EXPIRED = 'expired';


    public static function createForUser(User $user, string $sessionId, array $deviceInfo = []): array
    {
        $refreshToken = Str::random(64);

        $model = self::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays((int)config('tokens.refresh_lifetime', 30)),
            'status' => self::STATUS_ACTIVE,
            'used' => false,
            'device_name' => $deviceInfo['device_name'] ?? null,
            'browser' => $deviceInfo['browser'] ?? null,
            'ip_address' => $deviceInfo['ip_address'] ?? null,
        ]);

        return [
            'model' => $model,
            'refreshToken' => $refreshToken,
        ];
    }
    
    // cancel token
    public function revoke(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REVOKED,
            'revoked_at' => now(),
            'revoked_reason' => $reason
        ]);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->expires_at->isFuture();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
