<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Only exclude if absolutely necessary
    ];

    /**
     * Determine if the session and input CSRF tokens match.
     */
    protected function tokensMatch($request): bool
    {
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            Log::warning('CSRF token missing', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
            ]);
            return false;
        }

        $isValid = is_string($request->session()->token()) &&
                   is_string($token) &&
                   hash_equals($request->session()->token(), $token);

        if (!$isValid) {
            Log::warning('CSRF token mismatch', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
            ]);
        }

        return $isValid;
    }

    /**
     * Handle CSRF token mismatch
     */
    protected function handleTokenMismatch($request, $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'CSRF token mismatch. Please refresh the page and try again.'
            ], 419);
        }

        return parent::handleTokenMismatch($request, $exception);
    }
}

