@extends('layouts.app')

@section('title', 'View User Password')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a></li>
                <li class="breadcrumb-item active">View Password</li>
            </ol>
        </nav>
        <h2 class="fw-bold"><i class="bi bi-eye"></i> Password for {{ $user->name }}</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Security Notice</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-shield-exclamation"></i> 
                        <strong>Important:</strong> This password is displayed temporarily and will expire in 1 hour. 
                        Please copy it securely and inform the user. For security reasons, this password will not be shown again after the cache expires.
                    </div>

                    <div class="mb-4">
                        <label for="password-display" class="form-label fw-bold">Current Temporary Password:</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg font-monospace" 
                                   id="password-display" value="{{ $tempPassword }}" readonly 
                                   style="font-size: 1.2rem; font-weight: bold;">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyPassword()">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                <i class="bi bi-eye" id="toggle-icon"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted mt-2">
                            Click the copy button to copy the password to your clipboard.
                        </small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to User
                        </a>
                        <div>
                            <a href="{{ route('users.password.reset', $user) }}" class="btn btn-info">
                                <i class="bi bi-key"></i> Reset Password Again
                            </a>
                            <button type="button" class="btn btn-success" onclick="copyPassword()">
                                <i class="bi bi-clipboard-check"></i> Copy Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Role:</strong> <span class="badge bg-primary">{{ $user->roles->first()->name ?? 'No Role' }}</span></p>
                    <p><strong>Status:</strong> 
                        @if($user->status == 'verified')
                        <span class="badge bg-success">Verified</span>
                        @elseif($user->status == 'pending')
                        <span class="badge bg-warning">Pending</span>
                        @else
                        <span class="badge bg-danger">Rejected</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Instructions</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0 small">
                        <li>Copy the password using the copy button</li>
                        <li>Share it securely with the user</li>
                        <li>Advise the user to change it after first login</li>
                        <li>This password expires in 1 hour</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyPassword() {
    const passwordInput = document.getElementById('password-display');
    passwordInput.select();
    passwordInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        
        // Show success feedback
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i> Copied!';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    } catch (err) {
        alert('Failed to copy password. Please select and copy manually.');
    }
}

function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password-display');
    const toggleIcon = document.getElementById('toggle-icon');
    
    if (passwordInput.type === 'text') {
        passwordInput.type = 'password';
        toggleIcon.className = 'bi bi-eye';
    } else {
        passwordInput.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
    }
}
</script>
@endsection
