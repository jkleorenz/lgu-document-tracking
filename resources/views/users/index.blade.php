@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<style>
    /* Uniform Action Buttons */
    .btn-group .btn-sm {
        min-width: 36px;
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px !important;
        margin: 0 3px;
    }
    
    .btn-group .btn-sm i {
        font-size: 1rem;
        margin: 0;
    }
    
    .btn-group {
        gap: 0;
        display: flex;
        align-items: center;
    }
    
    .btn-group form {
        margin: 0;
        display: inline-flex;
    }
    
    /* Override Bootstrap btn-group styles that connect buttons */
    .btn-group .btn-sm:first-child,
    .btn-group .btn-sm:not(:first-child),
    .btn-group .btn-sm:last-child {
        border-radius: 8px !important;
    }
    
    /* Custom Pagination - No Arrows, Text Only */
    .pagination {
        font-size: 0.875rem;
        gap: 4px;
    }
    
    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        min-width: 40px;
        text-align: center;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        color: #495057;
        transition: all 0.2s ease;
        font-weight: 500;
    }
    
    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
        color: #212529;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
        font-weight: 600;
    }
    
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Previous/Next text styling */
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        padding: 0.5rem 1rem;
    }
</style>

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
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="white-space: nowrap;">NAME</th>
                            <th style="white-space: nowrap;">EMAIL</th>
                            <th class="text-center" style="white-space: nowrap;">DEPARTMENT</th>
                            <th class="text-center" style="white-space: nowrap;">ROLE</th>
                            <th class="text-center" style="white-space: nowrap;">STATUS</th>
                            <th class="text-center" style="white-space: nowrap;">JOINED</th>
                            <th class="text-center" style="white-space: nowrap;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center">{{ $user->department->name ?? 'N/A' }}</td>
                            <td class="text-center"><span class="badge bg-primary">{{ $user->roles->first()->name ?? 'No Role' }}</span></td>
                            <td class="text-center">
                                @if($user->status == 'verified')
                                <span class="badge bg-success">Verified</span>
                                @elseif($user->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                                @else
                                <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td class="text-center"><small>{{ $user->created_at->format('M d, Y') }}</small></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id != auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this user?')">
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

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
                </div>
                <nav aria-label="User pagination">
                    <ul class="pagination mb-0">
                        {{-- Previous Page Link --}}
                        @if ($users->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev">Previous</a>
                            </li>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                            @if ($page == $users->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($users->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Next</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

