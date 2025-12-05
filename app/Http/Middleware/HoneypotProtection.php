<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HoneypotProtection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check POST, PUT, PATCH, DELETE requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Check for honeypot field (should be empty - bots often fill all fields)
            $honeypotFields = ['website', 'url', 'homepage', 'home_page'];
            
            foreach ($honeypotFields as $field) {
                if ($request->has($field) && !empty($request->input($field))) {
                    // Bot detected - log and silently fail
                    Log::warning('Honeypot triggered', [
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'field' => $field,
                        'value' => $request->input($field),
                        'user_agent' => $request->userAgent(),
                    ]);
                    
                    // Return success response to avoid alerting the bot
                    return response('', 200);
                }
            }
        }
        
        return $next($request);
    }
}








