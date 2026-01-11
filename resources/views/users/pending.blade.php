@extends('layouts.app')

@section('title', 'Pending User Verifications')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">Pending Verifications</li>
            </ol>
        </nav>
        <h2 class="fw-bold"><i class="bi bi-person-exclamation"></i> Pending User Verifications</h2>
        <p class="text-muted">Review and approve user registration requests</p>
    </div>

    @if($pendingUsers->count() > 0)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> You have <strong>{{ $pendingUsers->count() }}</strong> pending user verification{{ $pendingUsers->count() > 1 ? 's' : '' }}
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Requested Role</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingUsers as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>{{ $user->department->name }}</td>
                            <td><span class="badge bg-secondary">{{ $user->roles->first()->name }}</span></td>
                            <td>
                                {{ $user->created_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <form method="POST" action="{{ route('users.verify', $user) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-circle"></i> Verify
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('users.reject', $user) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to reject this user?')">
                                            <i class="bi bi-x-circle"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle" style="font-size: 3rem; color: #198754;"></i>
            <h4 class="mt-3">All Caught Up!</h4>
            <p class="text-muted">There are no pending user verifications at this time.</p>
            <a href="{{ route('users.index') }}" class="btn btn-primary">
                <i class="bi bi-people"></i> View All Users
            </a>
        </div>
    </div>
    @endif
</div>
@endsection

