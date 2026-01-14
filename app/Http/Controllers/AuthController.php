<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\LoginAttempt;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Request as RequestFacade;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = $credentials['email'];
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Check progressive rate limiting (stricter after multiple failures)
        $rateLimitKey = $ipAddress.':'.$email;
        if (RateLimiter::tooManyAttempts('login-strict', 10)) {
            $seconds = RateLimiter::availableIn('login-strict');
            LoginAttempt::log($email, $ipAddress, false, $userAgent);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in ".ceil($seconds/60)." minutes.",
            ])->withInput($request->only('email'));
        }

        // Check if IP address is blocked due to brute force attempts
        if (LoginAttempt::isIpBlocked($ipAddress)) {
            LoginAttempt::log($email, $ipAddress, false, $userAgent);
            return back()->withErrors([
                'email' => 'Too many failed attempts from this IP address. Access temporarily blocked. Please try again later.',
            ])->withInput($request->only('email'));
        }

        // Check if user exists and is locked
        $user = User::where('email', $email)->first();
        
        if ($user && $user->isLocked()) {
            $remainingMinutes = now()->diffInMinutes($user->locked_until, false);
            
            LoginAttempt::log($email, $ipAddress, false, $userAgent);
            
            return back()->withErrors([
                'email' => "Your account has been locked due to multiple failed login attempts. Please try again in {$remainingMinutes} minutes or contact an administrator.",
            ])->withInput($request->only('email'));
        }

        // Attempt to authenticate user with remember me functionality
        $remember = $request->boolean('remember', false);
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if account is verified
            if ($user->status !== 'verified') {
                Auth::logout();
                LoginAttempt::log($email, $ipAddress, false, $userAgent);
                
                return back()->withErrors([
                    'email' => 'Your account is pending verification by an administrator.',
                ])->withInput($request->only('email'));
            }
            
            // Reset failed attempts and update login info
            $user->resetFailedAttempts();
            
            // Log successful login attempt
            LoginAttempt::log($email, $ipAddress, true, $userAgent);
            
            // Audit log
            AuditLog::log('login', $user->id, null, null, "User logged in from {$ipAddress}");
            
            // Clear any stale intended URL and redirect to dashboard
            $intendedUrl = $request->session()->pull('url.intended');
            
            // Enhanced intended URL validation with whitelist approach
            if ($intendedUrl) {
                $urlPath = parse_url($intendedUrl, PHP_URL_PATH) ?? $intendedUrl;
                
                // Whitelist approach: only allow specific safe routes
                $allowedRoutes = [
                    '/dashboard',
                    '/documents',
                    '/profile',
                    '/notifications',
                    '/archive',
                    '/scan',
                    '/settings',
                ];
                
                $isAllowed = false;
                foreach ($allowedRoutes as $route) {
                    if (strpos($urlPath, $route) === 0) {
                        $isAllowed = true;
                        break;
                    }
                }
                
                // Additional security: validate it's a valid route and not an API endpoint
                if ($isAllowed && strpos($urlPath, '/api/') !== 0) {
                    try {
                        $route = Route::getRoutes()->match(RequestFacade::create($urlPath));
                        if ($route && !str_starts_with($urlPath, '/api/')) {
                            return redirect($intendedUrl);
                        }
                    } catch (\Exception $e) {
                        // Invalid route, ignore intended URL
                    }
                }
            }
            
            // Always redirect to dashboard as fallback
            return redirect()->route('dashboard');
        }

        // Failed login attempt
        if ($user) {
            $user->incrementFailedAttempts();
            
            $errorMessage = 'The provided credentials do not match our records.';
            if ($user->isLocked()) {
                $errorMessage .= ' Your account has been temporarily locked due to multiple failed attempts.';
            }
        } else {
            $errorMessage = 'The provided credentials do not match our records.';
        }

        // Log failed login attempt
        LoginAttempt::log($email, $ipAddress, false, $userAgent);
        
        // Increment progressive rate limiter on failure
        RateLimiter::hit('login-strict');

        return back()->withErrors([
            'email' => $errorMessage,
        ])->withInput($request->only('email'));
    }

    /**
     * Show the registration form
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        // Try to get departments, but handle case where table doesn't exist yet
        try {
            $departments = Department::active()->orderBy('name')->get();
        } catch (\Exception $e) {
            // If departments table doesn't exist (migrations not run), return empty collection
            // This allows the page to load but registration won't work until migrations are run
            $departments = collect([]);
        }
        
        return view('auth.register', compact('departments'));
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        // Validate registration data with enhanced password requirements
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required', 
                'confirmed', 
                Password::min(12) // Increased from 8 to 12
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised() // Check against breached passwords database
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'role' => ['required', 'in:LGU Staff,Department Head'],
        ]);

        // Create new user with pending status
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'],
            'status' => 'pending',
        ]);

        // Assign role to user
        $user->assignRole($validated['role']);

        // Audit log
        AuditLog::log('user.registered', null, User::class, $user->id, "New user registered: {$user->email}", null, ['email' => $user->email, 'name' => $user->name], $request->ip(), $request->userAgent());

        return redirect()->route('login')->with('success', 
            'Registration successful! Please wait for administrator approval before logging in.');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Audit log before logout
        if ($user) {
            AuditLog::log('logout', $user->id, null, null, "User logged out", null, null, $request->ip(), $request->userAgent());
        }
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}

