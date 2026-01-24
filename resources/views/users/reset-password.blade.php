@extends('layouts.app')

@section('title', 'Reset User Password')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a></li>
                <li class="breadcrumb-item active">Reset Password</li>
            </ol>
        </nav>
        <h2 class="fw-bold"><i class="bi bi-key"></i> Reset Password for {{ $user->name }}</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Password Reset Form</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Note:</strong> After resetting the password, you will be able to view the new password. 
                        The password will be stored temporarily for 1 hour for viewing purposes.
                    </div>

                    <form method="POST" action="{{ route('users.password.reset.store', $user) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required
                                       aria-describedby="password-requirements password-help">
                                <button type="button" 
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                        id="togglePassword" 
                                        aria-label="Show password"
                                        style="border: none; background: none; padding: 0; z-index: 10;">
                                    <i class="bi bi-eye" id="passwordToggleIcon"></i>
                                </button>
                            </div>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="password-requirements" class="mt-2" role="group" aria-label="Password requirements">
                                <div class="password-requirement" data-requirement="length">
                                    <i class="bi bi-circle" aria-hidden="true"></i>
                                    <span>Minimum 8 characters</span>
                                </div>
                                <div class="password-requirement" data-requirement="uppercase">
                                    <i class="bi bi-circle" aria-hidden="true"></i>
                                    <span>At least 1 uppercase letter</span>
                                </div>
                                <div class="password-requirement" data-requirement="number">
                                    <i class="bi bi-circle" aria-hidden="true"></i>
                                    <span>At least 1 number</span>
                                </div>
                                <div class="password-requirement" data-requirement="symbol">
                                    <i class="bi bi-circle" aria-hidden="true"></i>
                                    <span>At least 1 symbol</span>
                                </div>
                            </div>
                            <small id="password-help" class="form-text text-muted d-block mt-1">Password requirements</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required
                                       aria-describedby="password-match-feedback">
                                <button type="button" 
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                        id="togglePasswordConfirmation" 
                                        aria-label="Show password"
                                        style="border: none; background: none; padding: 0; z-index: 10;">
                                    <i class="bi bi-eye" id="passwordConfirmationToggleIcon"></i>
                                </button>
                            </div>
                            <div id="password-match-feedback" class="mt-1" role="status" aria-live="polite">
                                <small class="form-text text-muted">
                                    <i class="bi bi-circle" id="password-match-icon" aria-hidden="true"></i>
                                    <span id="password-match-text">Passwords must match</span>
                                </small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-key"></i> Reset Password
                            </button>
                        </div>
                    </form>
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
        </div>
    </div>
</div>

@push('scripts')
<style>
    /* Password Requirements Styling */
    .password-requirement {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 4px;
        transition: color 0.2s ease;
    }
    
    .password-requirement i {
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }
    
    .password-requirement.met {
        color: #198754;
    }
    
    .password-requirement.met i {
        color: #198754;
    }
    
    .password-requirement.met i.bi-check-circle {
        color: #198754;
    }
    
    /* Green outline for valid password */
    .form-control.is-valid-password {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
    }
    
    /* Password match indicator */
    #password-match-feedback .text-success {
        color: #198754 !important;
    }
    
    #password-match-feedback .text-danger {
        color: #dc3545 !important;
    }
    
    #password-match-icon.bi-check-circle {
        color: #198754;
    }
    
    #password-match-icon.bi-x-circle {
        color: #dc3545;
    }
    
    /* Eye icon button styling */
    #togglePassword,
    #togglePasswordConfirmation {
        color: #6c757d;
        text-decoration: none;
    }
    
    #togglePassword:hover,
    #togglePasswordConfirmation:hover {
        color: #495057;
    }
    
    #togglePassword:focus,
    #togglePasswordConfirmation:focus {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
        border-radius: 4px;
    }
</style>

<script>
(function() {
    'use strict';
    
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const togglePasswordConfirmationBtn = document.getElementById('togglePasswordConfirmation');
    const passwordToggleIcon = document.getElementById('passwordToggleIcon');
    const passwordConfirmationToggleIcon = document.getElementById('passwordConfirmationToggleIcon');
    
    if (!passwordInput || !passwordConfirmationInput) return;
    
    // Password validation rules
    const requirements = {
        length: {
            test: (value) => value.length >= 8,
            element: document.querySelector('[data-requirement="length"]')
        },
        uppercase: {
            test: (value) => /[A-Z]/.test(value),
            element: document.querySelector('[data-requirement="uppercase"]')
        },
        number: {
            test: (value) => /[0-9]/.test(value),
            element: document.querySelector('[data-requirement="number"]')
        },
        symbol: {
            test: (value) => /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value),
            element: document.querySelector('[data-requirement="symbol"]')
        }
    };
    
    // Validate password requirements
    function validatePassword(value) {
        let allMet = true;
        
        Object.keys(requirements).forEach(key => {
            const requirement = requirements[key];
            const isMet = requirement.test(value);
            
            if (requirement.element) {
                const icon = requirement.element.querySelector('i');
                if (isMet) {
                    requirement.element.classList.add('met');
                    if (icon) {
                        icon.className = 'bi bi-check-circle';
                    }
                    requirement.element.setAttribute('aria-label', requirement.element.textContent.trim() + ' - met');
                } else {
                    requirement.element.classList.remove('met');
                    if (icon) {
                        icon.className = 'bi bi-circle';
                    }
                    requirement.element.setAttribute('aria-label', requirement.element.textContent.trim() + ' - not met');
                }
            }
            
            if (!isMet) allMet = false;
        });
        
        return allMet;
    }
    
    // Validate password match
    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirmation = passwordConfirmationInput.value;
        const matchFeedback = document.getElementById('password-match-feedback');
        const matchIcon = document.getElementById('password-match-icon');
        const matchText = document.getElementById('password-match-text');
        
        if (!matchFeedback || !matchIcon || !matchText) return;
        
        if (confirmation.length === 0) {
            matchText.textContent = 'Passwords must match';
            matchIcon.className = 'bi bi-circle';
            matchFeedback.querySelector('small').classList.remove('text-success', 'text-danger');
            passwordConfirmationInput.classList.remove('is-valid-password');
            passwordConfirmationInput.setAttribute('aria-invalid', 'false');
            return false;
        }
        
        if (password === confirmation && password.length > 0) {
            matchText.textContent = 'Passwords match';
            matchIcon.className = 'bi bi-check-circle';
            matchFeedback.querySelector('small').classList.add('text-success');
            matchFeedback.querySelector('small').classList.remove('text-danger');
            passwordConfirmationInput.classList.add('is-valid-password');
            passwordConfirmationInput.setAttribute('aria-invalid', 'false');
            matchFeedback.setAttribute('aria-live', 'polite');
            return true;
        } else {
            matchText.textContent = 'Passwords do not match';
            matchIcon.className = 'bi bi-x-circle';
            matchFeedback.querySelector('small').classList.add('text-danger');
            matchFeedback.querySelector('small').classList.remove('text-success');
            passwordConfirmationInput.classList.remove('is-valid-password');
            passwordConfirmationInput.setAttribute('aria-invalid', 'true');
            matchFeedback.setAttribute('aria-live', 'assertive');
            return false;
        }
    }
    
    // Real-time password validation
    passwordInput.addEventListener('input', function() {
        const value = this.value;
        const isValid = validatePassword(value);
        
        // Apply green outline only when all requirements are met
        if (isValid && value.length > 0) {
            this.classList.add('is-valid-password');
            this.setAttribute('aria-invalid', 'false');
        } else {
            this.classList.remove('is-valid-password');
            this.setAttribute('aria-invalid', value.length > 0 ? 'true' : 'false');
        }
        
        // Re-validate match when password changes
        validatePasswordMatch();
    });
    
    // Real-time password confirmation validation
    passwordConfirmationInput.addEventListener('input', function() {
        validatePasswordMatch();
    });
    
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
    
    togglePasswordConfirmationBtn.addEventListener('click', function(e) {
        e.preventDefault();
        togglePasswordVisibility(passwordConfirmationInput, passwordConfirmationToggleIcon);
    });
    
    // Initialize validation on page load (for pre-filled values)
    if (passwordInput.value) {
        validatePassword(passwordInput.value);
    }
    validatePasswordMatch();
    
    // Form submission validation
    const form = passwordInput.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmation = passwordConfirmationInput.value;
            
            if (!validatePassword(password)) {
                e.preventDefault();
                passwordInput.focus();
                return false;
            }
            
            if (password !== confirmation) {
                e.preventDefault();
                passwordConfirmationInput.focus();
                return false;
            }
        });
    }
})();
</script>
@endpush
@endsection
