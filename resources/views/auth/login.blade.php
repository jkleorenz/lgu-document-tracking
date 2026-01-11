@extends('layouts.app')

@section('title', 'Login - LGU Document Tracking')

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
    .login-wrapper {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        min-height: 600px;
    }
    .login-wrapper > .row {
        display: flex;
        min-height: 100%;
    }
    .login-wrapper > .row > [class*="col-"] {
        display: flex;
    }
    .logo-section {
        background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 40px;
        position: relative;
        overflow: hidden;
        width: 100%;
        min-height: 100%;
    }
    .logo-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 30%, rgba(220, 38, 38, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 80% 70%, rgba(234, 179, 8, 0.1) 0%, transparent 50%);
        animation: pulse 10s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 0.6; }
        50% { opacity: 1; }
    }
    .logo-container {
        position: relative;
        z-index: 1;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .logo-image-wrapper {
        width: 100%;
        max-width: 380px;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 35px;
        position: relative;
    }
    .logo-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: drop-shadow(0 15px 40px rgba(0, 0, 0, 0.4));
        border-radius: 50%;
        background: transparent;
    }
    .logo-title {
        color: white;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 8px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        text-align: center;
        line-height: 1.3;
    }
    .logo-subtitle {
        color: rgba(255, 255, 255, 0.95);
        font-size: 0.95rem;
        text-align: center;
        text-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
        line-height: 1.4;
    }
    .login-section {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 50px 40px;
        min-height: 600px;
        width: 100%;
    }
    .login-header {
        text-align: center;
        margin-bottom: 35px;
    }
    .login-header h2 {
        color: #0f172a;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .login-header p {
        color: #64748b;
        font-size: 0.9rem;
        margin: 0;
    }
    .login-body {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
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
    .btn-login {
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
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(37, 99, 235, 0.5);
    }
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 18px 0;
    }
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }
    .divider span {
        padding: 0 10px;
        color: #94a3b8;
        font-size: 0.875rem;
    }
    .footer-text {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    .footer-text small {
        color: #94a3b8;
        font-size: 0.8125rem;
    }
    
    /* Responsive Design */
    @media (max-width: 991.98px) {
        .login-wrapper > .row {
            flex-direction: column;
        }
        .logo-section {
            padding: 50px 30px;
            min-height: 350px;
        }
        .logo-image-wrapper {
            max-width: 280px;
            margin-bottom: 25px;
        }
        .logo-title {
            font-size: 1.5rem;
        }
        .logo-subtitle {
            font-size: 0.9rem;
        }
        .login-section {
            padding: 40px 30px;
            min-height: auto;
        }
        .login-wrapper {
            min-height: auto;
        }
    }
    @media (max-width: 575.98px) {
        .logo-section {
            padding: 40px 20px;
            min-height: 300px;
        }
        .logo-image-wrapper {
            max-width: 220px;
            margin-bottom: 20px;
        }
        .logo-title {
            font-size: 1.25rem;
        }
        .logo-subtitle {
            font-size: 0.85rem;
        }
        .login-section {
            padding: 30px 20px;
        }
        .login-header h2 {
            font-size: 1.5rem;
        }
    }
</style>

<div class="container-fluid px-3">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-12 col-lg-10 col-xl-9">
            <div class="login-wrapper">
                <div class="row g-0">
                    <!-- Logo Section -->
                    <div class="col-12 col-lg-5">
                        <div class="logo-section">
                            <div class="logo-container">
                                <div class="logo-image-wrapper">
                                    <img src="{{ asset('logo..png') }}" alt="Municipality of Bontoc Logo" class="logo-image" onerror="this.onerror=null; this.src='{{ asset('logo.png') }}';">
                                </div>
                                <h1 class="logo-title">Municipality of Bontoc</h1>
                                <p class="logo-subtitle">Province of Southern Leyte<br>Document Tracking System</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Login Form Section -->
                    <div class="col-12 col-lg-7">
                        <div class="login-section">
                            <div class="login-header">
                                <h2>Welcome Back</h2>
                                <p>Sign in to continue to your account</p>
                            </div>

                            <div class="login-body">
                                @if(session('success'))
                                <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius: 10px; padding: 0.65rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                                </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf

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
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-floating position-relative">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password"
                                               placeholder="Password" 
                                               required>
                                        <button type="button" 
                                                class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                                id="togglePassword" 
                                                aria-label="Show password"
                                                style="border: none; background: none; padding: 0; z-index: 10;">
                                            <i class="bi bi-eye" id="passwordToggleIcon"></i>
                                        </button>
                                        <label for="password"><i class="bi bi-lock"></i> Password</label>
                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                        <label class="form-check-label" for="remember" style="font-size: 0.9375rem;">Remember me</label>
                                    </div>

                                    <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                                        <i class="bi bi-box-arrow-in-right"></i> Sign In
                                    </button>
                                </form>

                                <div class="footer-text">
                                    <small>&copy; {{ date('Y') }} LGU Document Tracking System. All rights reserved.</small>
                                </div>
                            </div>
                        </div>
                    </div>
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

