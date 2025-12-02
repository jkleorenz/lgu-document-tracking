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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Profile Picture -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-image"></i> Profile Picture</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3" id="profile-picture-container" style="position: relative; display: inline-block; width: 150px; height: 150px;">
                        <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) . '?v=' . time() : '' }}" 
                             alt="Profile Picture" 
                             id="profile-picture-img"
                             class="rounded-circle" 
                             style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #dee2e6; position: absolute; top: 0; left: 0; {{ $user->profile_picture ? 'display: block;' : 'display: none;' }} z-index: 2;"
                             onerror="handleImageError();">
                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                             id="profile-picture-placeholder"
                             style="width: 150px; height: 150px; border: 3px solid #dee2e6; position: absolute; top: 0; left: 0; {{ $user->profile_picture ? 'display: none;' : 'display: flex;' }} z-index: 1;">
                            <i class="bi bi-person" style="font-size: 4rem; color: white;"></i>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.picture') }}" enctype="multipart/form-data" class="mb-2" id="profile-picture-form">
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
                            <form method="POST" action="{{ route('profile.picture.remove') }}" class="d-inline" id="remove-picture-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove your profile picture?')">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </form>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Update Your Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
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
                        <li>Your profile picture</li>
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

@push('scripts')
<script>
function handleImageError() {
    const img = document.getElementById('profile-picture-img');
    const placeholder = document.getElementById('profile-picture-placeholder');
    if (img) {
        img.style.display = 'none';
    }
    if (placeholder) {
        placeholder.style.display = 'flex';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const profilePictureInput = document.getElementById('profile_picture');
    const profilePictureContainer = document.getElementById('profile-picture-container');
    const profilePictureImg = document.getElementById('profile-picture-img');
    const profilePicturePlaceholder = document.getElementById('profile-picture-placeholder');
    
    // Show preview when file is selected
    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, or GIF)');
                    this.value = '';
                    return;
                }
                
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Ensure image element exists
                    let img = profilePictureImg;
                    if (!img) {
                        img = document.createElement('img');
                        img.id = 'profile-picture-img';
                        img.className = 'rounded-circle';
                        img.alt = 'Profile Picture';
                        img.style.cssText = 'width: 150px; height: 150px; object-fit: cover; border: 3px solid #dee2e6; position: absolute; top: 0; left: 0; display: block; z-index: 2;';
                        img.onerror = handleImageError;
                        if (profilePictureContainer) {
                            profilePictureContainer.appendChild(img);
                        }
                    }
                    
                    // Hide placeholder first (ensure only one is visible)
                    if (profilePicturePlaceholder) {
                        profilePicturePlaceholder.style.display = 'none';
                    }
                    
                    // Update and show image (centered in the same position as placeholder)
                    img.src = event.target.result;
                    img.style.display = 'block';
                    img.style.position = 'absolute';
                    img.style.top = '0';
                    img.style.left = '0';
                    img.style.zIndex = '2';
                };
                reader.onerror = function() {
                    alert('Error reading file. Please try again.');
                };
                reader.readAsDataURL(file);
            } else {
                // If no file selected, restore original state
                if (profilePictureImg && !profilePictureImg.src) {
                    profilePictureImg.style.display = 'none';
                    if (profilePicturePlaceholder) {
                        profilePicturePlaceholder.style.display = 'flex';
                    }
                }
            }
        });
    }
    
    // Handle form submission with AJAX for better UX
    const profilePictureForm = document.getElementById('profile-picture-form');
    if (profilePictureForm) {
        profilePictureForm.addEventListener('submit', function(e) {
            const submitButton = profilePictureForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Uploading...';
            }
        });
    }
    
    // Refresh image after successful upload
    @if(session('success') && session('picture_updated'))
        // Force reload the page to show the new image with cache busting
        setTimeout(function() {
            // Add cache busting parameter to force reload
            window.location.href = window.location.href.split('?')[0] + '?v=' + new Date().getTime();
        }, 300);
    @endif
    
    // Ensure image loads properly on page load
    if (profilePictureImg && profilePictureImg.src) {
        profilePictureImg.onload = function() {
            if (profilePicturePlaceholder) {
                profilePicturePlaceholder.style.display = 'none';
            }
            this.style.display = 'block';
        };
        profilePictureImg.onerror = function() {
            handleImageError();
        };
        
        // If image has a src, ensure it's visible and placeholder is hidden
        if (profilePictureImg.src && profilePictureImg.src !== window.location.href) {
            profilePictureImg.style.display = 'block';
            if (profilePicturePlaceholder) {
                profilePicturePlaceholder.style.display = 'none';
            }
        }
    }
});
</script>
@endpush
@endsection

