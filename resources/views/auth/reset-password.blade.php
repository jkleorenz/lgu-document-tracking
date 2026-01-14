@extends('layouts.app')

@section('title', 'Reset Password - LGU Document Tracking')

@section('content')
<style>
    body {
        background: #f1f5f9;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px 0;
    }
    .password-reset-wrapper {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
    }
    .password-reset-container {
        max-width: 500px;
        width: 100%;
        padding: 50px 40px;
    }
    .reset-header {
        text-align: center;
        margin-bottom: 35px;
    }
    .reset-header h2 {
        color: #0f172a;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .reset-header p {
        color: #64748b;
        font-size: 0.95rem;
        margin: 0;
        line-height: 1.5;
    }
    .form-floating {
        margin-bottom: 15px;
    }
    .form-floating .form-control {
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 0.65rem;
        height: calc(3.125rem + 2px);
        font-size: 0.9375rem;
        transition: all 0.3s;
    }
    .form-floating .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    .form-floating label {
        padding: 0.75rem 0.65rem;
        font-size: 0.9375rem;
    }
    .btn-reset {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        border: none;
        border-radius: 10px;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 0.9375rem;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.4);
        transition: all 0.3s;
        min-height: 44px;
    }
    .btn-reset:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(37, 99, 235, 0.5);
    }
    .back-to-login {
        text-align: center;
        margin-top: 20px;
    }
    .back-to-login a {
        color: #2563eb;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: color 0.3s;
    }
    .back-to-login a:hover {
        color: #1d4ed8;
    }
    .alert {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        margin-bottom: 20px;
        border: none;
    }
    .alert-success {
        background-color: #ecfdf5;
        color: #065f46;
    }
    .alert-danger {
        background-color: #fef2f2;
        color: #7f1d1d;
    }
    .password-strength {
        margin-top: 5px;
        font-size: 0.75rem;
    }
    .password-strength .indicator {
        display: inline-block;
        width: 3px;
        height: 3px;
        border-radius: 50%;
        margin-right: 4px;
        background-color: #d1d5db;
    }
    .password-strength.weak .indicator:nth-child(1) { background-color: #ef4444; }
    .password-strength.fair .indicator:nth-child(-n+2) { background-color: #f59e0b; }
    .password-strength.good .indicator:nth-child(-n+3) { background-color: #3b82f6; }
    .password-strength.strong .indicator { background-color: #10b981; }
    @media (max-width: 575.98px) {
        .password-reset-container {
            padding: 35px 25px;
        }
        .reset-header h2 {
            font-size: 1.5rem;
        }
        .reset-header p {
            font-size: 0.9rem;
        }
    }
</style>

<div class="container-fluid px-3">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-12 col-sm-10 col-md-8">
            <div class="password-reset-wrapper">
                <div class="password-reset-container mx-auto">
                    <div class="reset-header">
                        <h2><i class="bi bi-shield-check"></i> Reset Your Password</h2>
                        <p>Create a strong new password for your account</p>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        @if($errors->has('token'))
                            {{ $errors->first('token') }}
                        @else
                            Please correct the errors below
                        @endif
                    </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" id="resetForm">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-floating">
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   placeholder="name@example.com"
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus>
                            <label for="email"><i class="bi bi-envelope"></i> Email Address</label>
                            @error('email')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating position-relative">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password"
                                   placeholder="New Password" 
                                   required
                                   minlength="8">
                            <button type="button" 
                                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                    id="togglePassword" 
                                    aria-label="Show password"
                                    style="border: none; background: none; padding: 0; z-index: 10;">
                                <i class="bi bi-eye" id="passwordToggleIcon"></i>
                            </button>
                            <label for="password"><i class="bi bi-lock"></i> New Password</label>
                            @error('password')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                            <small style="color: #64748b; margin-top: 3px; display: block;">
                                • At least 8 characters<br>
                                • Mix of uppercase and lowercase letters<br>
                                • At least one number and special character
                            </small>
                        </div>

                        <div class="form-floating">
                            <input type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation"
                                   placeholder="Confirm Password" 
                                   required>
                            <label for="password_confirmation"><i class="bi bi-lock-check"></i> Confirm Password</label>
                            @error('password_confirmation')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-reset btn-primary w-100 mt-4">
                            <i class="bi bi-check-circle"></i> Reset Password
                        </button>

                        <div class="back-to-login">
                            <a href="{{ route('login') }}">
                                <i class="bi bi-arrow-left"></i> Back to Login
                            </a>
                        </div>
                    </form>

                    <hr style="margin: 30px 0; border-color: #e2e8f0;">

                    <p style="text-align: center; color: #94a3b8; font-size: 0.8125rem; margin: 0;">
                        &copy; {{ date('Y') }} LGU Document Tracking System. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    'use strict';
    
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordToggleIcon = document.getElementById('passwordToggleIcon');
    
    if (!passwordInput || !togglePasswordBtn) return;
    
    // Toggle password visibility
    function togglePasswordVisibility(input, icon) {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        
        if (type === 'password') {
            icon.className = 'bi bi-eye';
            icon.parentElement.setAttribute('aria-label', 'Show password');
        } else {
            icon.className = 'bi bi-eye-slash';
            icon.parentElement.setAttribute('aria-label', 'Hide password');
        }
    }
    
    togglePasswordBtn.addEventListener('click', function(e) {
        e.preventDefault();
        togglePasswordVisibility(passwordInput, passwordToggleIcon);
    });
})();
</script>
@endpush
@endsection
