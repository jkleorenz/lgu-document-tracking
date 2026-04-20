@extends('layouts.app')

@section('title', 'Login - LGU Document Tracking')

@section('content')
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
                                        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember" style="font-size: 0.9375rem;">Remember me</label>
                                    </div>

                                    <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                                        <i class="bi bi-box-arrow-in-right"></i> Sign In
                                    </button>
                                </form>

                                <div class="footer-text">
                                    <small>
                                        &copy; {{ date('Y') }} LGU Document Tracking System. All rights reserved.
                                        <span style="display: block; margin-top: 4px; color: #2563eb; font-weight: 600;">
                                            Developed by SLSU BSIT Students
                                            <i class="bi bi-patch-check-fill" style="margin-left: 4px;"></i>
                                        </span>
                                    </small>
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

