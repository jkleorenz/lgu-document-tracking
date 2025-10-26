@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-people"></i> User Management</h2>
        <div>
            @if($pendingCount > 0)
            <a href="{{ route('users.pending') }}" class="btn btn-warning me-2">
                <i class="bi bi-person-exclamation"></i> Pending Verifications
                <span class="badge bg-dark">{{ $pendingCount }}</span>
            </a>
            @endif
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Add New User
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search users..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">All Roles</option>
                            <option value="Administrator" {{ request('role') == 'Administrator' ? 'selected' : '' }}>Administrator</option>
                            <option value="LGU Staff" {{ request('role') == 'LGU Staff' ? 'selected' : '' }}>LGU Staff</option>
                            <option value="Department Head" {{ request('role') == 'Department Head' ? 'selected' : '' }}>Department Head</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department->name ?? 'N/A' }}</td>
                            <td><span class="badge bg-primary">{{ $user->roles->first()->name ?? 'No Role' }}</span></td>
                            <td>
                                @if($user->status == 'verified')
                                <span class="badge bg-success">Verified</span>
                                @elseif($user->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                                @else
                                <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td><small>{{ $user->created_at->format('M d, Y') }}</small></td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id != auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No users found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

