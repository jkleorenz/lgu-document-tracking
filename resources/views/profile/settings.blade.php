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
                    <div class="mb-3">
                        @if($user->profile_picture)
                            <img src="{{ Storage::disk('public')->url($user->profile_picture) }}" 
                                 alt="Profile Picture" 
                                 class="rounded-circle" 
                                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #dee2e6;">
                        @else
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px; border: 3px solid #dee2e6;">
                                <i class="bi bi-person" style="font-size: 4rem; color: white;"></i>
                            </div>
                        @endif
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
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Minimum 8 characters required.</small>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required>
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
@endsection

