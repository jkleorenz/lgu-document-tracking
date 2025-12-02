@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Profile</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold"><i class="bi bi-person-circle"></i> My Profile</h2>
            <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Profile
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($user->profile_picture)
                            <img src="{{ Storage::disk('public')->url($user->profile_picture) }}" 
                                 alt="Profile Picture" 
                                 class="rounded-circle" 
                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #dee2e6;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                 style="width: 120px; height: 120px; font-size: 3rem; border: 3px solid #dee2e6;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->roles->first()->name ?? 'No Role' }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-{{ $user->status == 'verified' ? 'success' : 'warning' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                        <a href="{{ route('settings') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-info">
                            <i class="bi bi-bell"></i> Notifications
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Personal Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Full Name:</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email Address:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone Number:</th>
                            <td>{{ $user->phone ?? 'Not provided' }}</td>
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
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $user->status == 'verified' ? 'success' : 'warning' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Member Since:</th>
                            <td>{{ $user->created_at->format('F d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Activity Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        @if($user->hasRole('LGU Staff'))
                        <div class="col-md-4">
                            <div class="p-3">
                                <h3 class="text-primary">{{ $user->createdDocuments()->count() }}</h3>
                                <small class="text-muted">Documents Created</small>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <div class="p-3">
                                <h3 class="text-success">{{ $user->statusLogs()->count() }}</h3>
                                <small class="text-muted">Status Updates</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <h3 class="text-info">{{ $user->notifications()->count() }}</h3>
                                <small class="text-muted">Total Notifications</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

