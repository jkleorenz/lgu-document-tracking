@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Verify OTP</h4>
                </div>
                <div class="card-body p-5">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if ($message = Session::get('success'))
                        <div class="alert alert-success">
                            {{ $message }}
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        We've sent a 6-digit OTP to your email. Please enter it below.
                    </p>

                    <form action="{{ route('password.verify-otp') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email Address</label>
                            <input 
                                type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                placeholder="Enter your email"
                                required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="otp" class="form-label fw-bold">One-Time Password (OTP)</label>
                            <input 
                                type="text" 
                                class="form-control @error('otp') is-invalid @enderror" 
                                id="otp" 
                                name="otp" 
                                placeholder="Enter 6-digit OTP"
                                maxlength="6"
                                pattern="\d{6}"
                                inputmode="numeric"
                                required
                                autofocus>
                            <small class="form-text text-muted d-block mt-2">
                                OTP expires in 10 minutes
                            </small>
                            @error('otp')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                            Verify OTP
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted mb-2">Didn't receive the OTP?</p>
                        <a href="{{ route('password.request') }}" class="btn btn-outline-primary btn-sm">
                            Request New OTP
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
