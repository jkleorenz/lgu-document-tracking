<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;
use Symfony\Component\HttpFoundation\Response;

class SecurityMonitoring
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log suspicious patterns
        $suspiciousPatterns = [
            '/\.\.\//' => 'Path traversal attempt',
            '/<script/i' => 'XSS attempt',
            '/union.*select/i' => 'SQL injection attempt',
            '/exec\(/i' => 'Command injection attempt',
            '/eval\(/i' => 'Code injection attempt',
            '/base64_decode/i' => 'Obfuscation attempt',
            '/\.\.\\\\/' => 'Path traversal attempt (Windows)',
        ];

        // Build request string for pattern checking
        // Exclude file uploads from serialization (they can't be serialized)
        $requestData = $request->except(array_keys($request->allFiles()));
        $requestString = $request->fullUrl() . json_encode($requestData);
        
        // Also check file names if files are present
        foreach ($request->allFiles() as $file) {
            if (is_array($file)) {
                foreach ($file as $singleFile) {
                    if ($singleFile && method_exists($singleFile, 'getClientOriginalName')) {
                        $requestString .= ' ' . $singleFile->getClientOriginalName();
                    }
                }
            } else {
                if ($file && method_exists($file, 'getClientOriginalName')) {
                    $requestString .= ' ' . $file->getClientOriginalName();
                }
            }
        }
        
        foreach ($suspiciousPatterns as $pattern => $description) {
            if (preg_match($pattern, $requestString)) {
                Log::warning('Suspicious request detected', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'pattern' => $description,
                    'user_agent' => $request->userAgent(),
                    'method' => $request->method(),
                ]);
                
                if (Auth::check()) {
                    AuditLog::log('security.suspicious_activity', Auth::id(), null, null, 
                        "Suspicious pattern detected: {$description}", null, null, $request->ip(), $request->userAgent());
                }
            }
        }

        return $next($request);
    }
}

