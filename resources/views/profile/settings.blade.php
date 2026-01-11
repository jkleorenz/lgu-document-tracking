@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </nav>
        <h2 class="fw-bold"><i class="bi bi-gear"></i> Account Settings</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Profile Picture -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-image"></i> Profile Picture</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3" style="position: relative; display: inline-block; width: 150px; height: 150px;">
                        <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : '' }}" 
                             alt="Profile Picture" 
                             id="profile-picture-img"
                             class="rounded-circle" 
                             style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #dee2e6; position: absolute; top: 0; left: 0; {{ $user->profile_picture ? 'display: block;' : 'display: none;' }} z-index: 2;"
                             onerror="this.style.display='none'; document.getElementById('profile-picture-placeholder').style.display='flex';">
                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                             id="profile-picture-placeholder"
                             style="width: 150px; height: 150px; border: 3px solid #dee2e6; position: absolute; top: 0; left: 0; {{ $user->profile_picture ? 'display: none;' : 'display: flex;' }} z-index: 1;">
                            <i class="bi bi-person" style="font-size: 4rem; color: white;"></i>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.picture') }}" enctype="multipart/form-data" class="mb-2">
                        @csrf
                        <div class="mb-3">
                            <input type="file" 
                                   class="form-control @error('profile_picture') is-invalid @enderror" 
                                   id="profile_picture" 
                                   name="profile_picture" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif">
                            @error('profile_picture')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Max size: 2MB. Formats: JPG, PNG, GIF</small>
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-upload"></i> Upload Picture
                            </button>
                            @if($user->profile_picture)
                            <form method="POST" action="{{ route('profile.picture.remove') }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove your profile picture?')">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </form>
                            @endif
                        </div>
                    </form>
                    <div class="mt-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.password') }}">
                        @csrf
                        @method('PUT')

                        <!-- Current Password -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password" 
                                   required>
                            @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- New Password -->
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

                        <!-- Confirm New Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
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

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Account Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Account Status:</th>
                            <td>
                                <span class="badge bg-{{ $user->status == 'verified' ? 'success' : 'warning' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td><span class="badge bg-primary">{{ $user->roles->first()->name ?? 'No Role' }}</span></td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td>{{ $user->department->name ?? 'Not assigned' }}</td>
                        </tr>
                        <tr>
                            <th>Member Since:</th>
                            <td>{{ $user->created_at->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $user->updated_at->format('F d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Security Tips -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Security Tips</h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-2">Password Best Practices:</h6>
                    <ul class="small">
                        <li>Use at least 8 characters</li>
                        <li>Mix uppercase and lowercase letters</li>
                        <li>Include numbers and special characters</li>
                        <li>Don't reuse old passwords</li>
                        <li>Don't share your password with anyone</li>
                        <li>Change your password regularly</li>
                    </ul>

                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Important:</strong> You will be logged out after changing your password.
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-person"></i> View Profile
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-bell"></i> Notifications
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </div>
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

