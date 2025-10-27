@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Profile</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h2 class="fw-bold"><i class="bi bi-pencil-square"></i> Edit Profile</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Update Your Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   placeholder="e.g., +63 912 345 6789">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Optional. Include country code if applicable.</small>
                        </div>

                        <!-- Read-only fields -->
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $user->roles->first()->name ?? 'No Role' }}" 
                                   readonly>
                            <small class="form-text text-muted">Contact administrator to change your role.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $user->department->name ?? 'Not assigned' }}" 
                                   readonly>
                            <small class="form-text text-muted">Contact administrator to change your department.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                            <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>What you can update:</strong></p>
                    <ul class="small">
                        <li>Your full name</li>
                        <li>Email address</li>
                        <li>Phone number</li>
                    </ul>
                    
                    <p class="mt-3"><strong>Cannot be changed:</strong></p>
                    <ul class="small">
                        <li>Role assignment</li>
                        <li>Department assignment</li>
                        <li>Account status</li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <i class="bi bi-lightbulb"></i> <strong>Tip:</strong> To change your password, go to <a href="{{ route('settings') }}">Settings</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

