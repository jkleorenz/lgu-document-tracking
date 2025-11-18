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
    }
    .login-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
    }
    .login-header {
        padding: 20px 30px 15px;
        text-align: center;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(249, 250, 251, 0.9) 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    .login-body {
        padding: 25px 30px;
    }
    .brand-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 12px;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
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
</style>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh; padding: 20px 0;">
        <div class="col-md-5 col-lg-4">
            <div class="login-card">
                <div class="login-header">
                    <div class="brand-icon">
                        <i class="bi bi-file-text text-white"></i>
                    </div>
                    <h2 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.5rem;">LGU DocTrack</h2>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Document Tracking System</p>
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

                        <div class="form-floating">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password"
                                   placeholder="Password" 
                                   required>
                            <label for="password"><i class="bi bi-lock"></i> Password</label>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember" style="font-size: 0.9375rem;">Remember me</label>
                        </div>

                        <button type="submit" class="btn btn-login btn-primary w-100 mb-2">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </button>

                        <div class="divider">
                            <span>OR</span>
                        </div>

                        <div class="text-center">
                            <p class="mb-0 text-muted" style="font-size: 0.875rem;">
                                Don't have an account? 
                                <a href="{{ route('register') }}" class="text-decoration-none fw-semibold" style="color: #2563eb;">
                                    Create Account
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <small class="text-white-50" style="font-size: 0.8125rem;">&copy; {{ date('Y') }} LGU Document Tracking System. All rights reserved.</small>
            </div>
        </div>
    </div>
</div>
@endsection

