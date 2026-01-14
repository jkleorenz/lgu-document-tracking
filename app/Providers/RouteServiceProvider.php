<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Enhanced login rate limiting: per email+IP combination
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email');
            $key = $email ? $request->ip().':'.$email : $request->ip();
            return Limit::perMinutes(15, 5)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Too many login attempts. Please try again later.'
                        ], 429)->withHeaders($headers);
                    }
                    return back()->withErrors([
                        'email' => 'Too many login attempts. Please try again in 15 minutes.',
                    ])->withInput($request->only('email'));
                });
        });

        // Progressive rate limiting: stricter after multiple failures
        RateLimiter::for('login-strict', function (Request $request) {
            $email = $request->input('email');
            $key = $email ? $request->ip().':'.$email : $request->ip();
            return Limit::perMinutes(60, 10)->by($key);
        });

        // Registration rate limiting: 3 attempts per hour per IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        // Password reset rate limiting: 3 attempts per hour per IP
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        // Forgot password rate limiting: 3 attempts per hour per email+IP
        RateLimiter::for('forgot-password', function (Request $request) {
            $email = $request->input('email');
            $key = $email ? $request->ip().':'.$email : $request->ip();
            return Limit::perHour(3)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    return back()->withErrors([
                        'email' => 'Too many password reset requests. Please try again in 1 hour.',
                    ])->withHeaders($headers);
                });
        });

        // Reset password rate limiting: 5 attempts per hour per IP
        RateLimiter::for('reset-password', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return back()->withErrors([
                        'token' => 'Too many password reset attempts. Please try again later.',
                    ])->withHeaders($headers);
                });
        });

        // General API rate limiting for authenticated users
        RateLimiter::for('api-authenticated', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}

