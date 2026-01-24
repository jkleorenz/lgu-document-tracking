<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send OTP to user email
     */
    public function sendOtp(Request $request)
    {
        // Validate email
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = $request->email;
        
        // Check if user exists and is verified
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'No account found with this email address.']);
        }

        if ($user->status !== 'verified') {
            return back()->withErrors(['email' => 'Your account is not yet verified by an administrator.']);
        }

        // Delete any existing OTP for this email
        Otp::where('email', $email)->delete();

        // Generate 6-digit OTP
        $otp = Otp::generateCode();

        // Store OTP with 10-minute expiration
        Otp::create([
            'email' => $email,
            'code' => Hash::make($otp), // Hash the OTP
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP via email
        try {
            Mail::to($email)->queue(new SendOtpMail($user, $otp));
            
            return back()->with('success', 'OTP has been sent to your email. It will expire in 10 minutes.');
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('OTP email failed: ' . $e->getMessage());
            
            // Delete the OTP if email fails
            Otp::where('email', $email)->delete();
            
            return back()->withErrors(['email' => 'Failed to send OTP. Please try again later.']);
        }
    }

    /**
     * Show OTP verification form
     */
    public function showOtpForm()
    {
        return view('auth.verify-otp');
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        // Validate OTP input
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = $request->email;
        $providedOtp = $request->otp;

        // Get the OTP record
        $otpRecord = Otp::where('email', $email)->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'No OTP found. Please request a new one.']);
        }

        // Check if OTP is expired
        if ($otpRecord->isExpired()) {
            $otpRecord->delete();
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        // Check if max attempts exceeded
        if ($otpRecord->hasExceededAttempts()) {
            $otpRecord->delete();
            return back()->withErrors(['otp' => 'Too many failed attempts. Please request a new OTP.']);
        }

        // Verify OTP
        if (!Hash::check($providedOtp, $otpRecord->code)) {
            $otpRecord->incrementAttempts();
            $remaining = 3 - $otpRecord->attempts;
            return back()->withErrors(['otp' => "Invalid OTP. {$remaining} attempts remaining."]);
        }

        // OTP is valid - delete it and redirect to password reset form
        $otpRecord->delete();

        return redirect()->route('password.reset.form', ['email' => $email])
            ->with('success', 'OTP verified successfully. Please reset your password.');
    }

    /**
     * Show password reset form
     */
    public function showResetForm(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Email is required.']);
        }

        // Verify user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('password.request')->withErrors(['email' => 'User not found.']);
        }

        return view('auth.reset-password-with-otp', ['email' => $email]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Find user and update password
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('login')
            ->with('success', 'Your password has been reset successfully. Please log in with your new password.');
    }
}
