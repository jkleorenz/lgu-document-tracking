<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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

        // Attempt to authenticate user
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if account is verified
            if ($user->status !== 'verified') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is pending verification by an administrator.',
                ])->withInput($request->only('email'));
            }
            
            // Clear any stale intended URL and redirect to dashboard
            $intendedUrl = $request->session()->pull('url.intended');
            
            // Only use intended URL if it's a valid web route (not an API endpoint)
            // Check if the intended URL is an API endpoint or invalid
            $isApiEndpoint = false;
            if ($intendedUrl) {
                // Normalize the URL for checking
                $urlPath = parse_url($intendedUrl, PHP_URL_PATH) ?? $intendedUrl;
                // Check if it starts with /api/ or contains /api/notifications/unread-count
                $isApiEndpoint = strpos($urlPath, '/api/') === 0 || 
                                strpos($urlPath, '/api/notifications/unread-count') !== false ||
                                strpos($intendedUrl, '/api/') !== false;
            }
            
            // Only redirect to intended URL if it's a valid web route (not an API endpoint)
            if ($intendedUrl && !$isApiEndpoint) {
                return redirect($intendedUrl);
            }
            
            // Always redirect to dashboard as fallback
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
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
        
        $departments = Department::active()->orderBy('name')->get();
        return view('auth.register', compact('departments'));
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        // Validate registration data
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
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

        return redirect()->route('login')->with('success', 
            'Registration successful! Please wait for administrator approval before logging in.');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}

