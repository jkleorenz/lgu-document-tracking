<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'ip_address',
        'success',
        'user_agent',
        'attempted_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    /**
     * Log a login attempt
     */
    public static function log(string $email, string $ipAddress, bool $success, ?string $userAgent = null): self
    {
        return self::create([
            'email' => $email,
            'ip_address' => $ipAddress,
            'success' => $success,
            'user_agent' => $userAgent,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Check if IP address is blocked due to too many failed attempts
     */
    public static function isIpBlocked(string $ipAddress, int $maxAttempts = 10, int $minutes = 15): bool
    {
        $recentFailures = self::where('ip_address', $ipAddress)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->count();
        
        return $recentFailures >= $maxAttempts;
    }

    /**
     * Get recent failed attempts count for an email
     */
    public static function getRecentFailedAttempts(string $email, int $hours = 1): int
    {
        return self::where('email', $email)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subHours($hours))
            ->count();
    }
}
