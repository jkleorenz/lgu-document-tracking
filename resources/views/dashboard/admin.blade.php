@extends('layouts.app')

@section('title', 'Administrator Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Administrator Dashboard</h2>
        <a href="{{ route('documents.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Document
        </a>
    </div>

    <!-- Document Statistics -->
    <div class="mb-3">
        <h5 class="text-muted mb-3"><i class="bi bi-file-text"></i> Document Overview</h5>
    </div>
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <a href="{{ route('documents.index', ['status' => 'Pending']) }}" class="text-decoration-none">
                <div class="card stat-card h-100 clickable-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-success bg-opacity-10">
                                <i class="bi bi-check-circle text-success"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                        </div>
                        <h6 class="text-muted mb-2">Active Documents</h6>
                        <h2 class="fw-bold mb-0 text-dark">{{ $activeDocuments }}</h2>
                        <small class="text-success"><i class="bi bi-arrow-right-circle"></i> View active</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <a href="{{ route('documents.index', ['priority' => '1']) }}" class="text-decoration-none">
                <div class="card stat-card h-100 clickable-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-danger bg-opacity-10">
                                <i class="bi bi-exclamation-triangle text-danger"></i>
                            </div>
                            @if($priorityDocuments > 0)
                            <span class="badge badge-priority">Urgent!</span>
                            @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">None</span>
                            @endif
                        </div>
                        <h6 class="text-muted mb-2">Priority Documents</h6>
                        <h2 class="fw-bold mb-0 text-dark">{{ $priorityDocuments }}</h2>
                        <small class="text-danger"><i class="bi bi-arrow-right-circle"></i> View priority</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <a href="{{ route('archive.index') }}" class="text-decoration-none">
                <div class="card stat-card h-100 clickable-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-secondary bg-opacity-10">
                                <i class="bi bi-archive text-secondary"></i>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Stored</span>
                        </div>
                        <h6 class="text-muted mb-2">Archived</h6>
                        <h2 class="fw-bold mb-0 text-dark">{{ $archivedDocuments }}</h2>
                        <small class="text-secondary"><i class="bi bi-arrow-right-circle"></i> View archive</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- User & System Statistics -->
    <div class="mb-3">
        <h5 class="text-muted mb-3"><i class="bi bi-people"></i> User Management</h5>
    </div>
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card stat-card h-100 {{ $pendingVerifications > 0 ? 'border-warning' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="stat-icon bg-warning bg-opacity-10">
                                    <i class="bi bi-person-check text-warning"></i>
                                </div>
                                <h6 class="text-muted mb-0">Pending Verifications</h6>
                            </div>
                            <h2 class="fw-bold mb-0">{{ $pendingVerifications }}</h2>
                            @if($pendingVerifications > 0)
                            <p class="text-warning mb-0 mt-2"><small><i class="bi bi-exclamation-circle"></i> Action required</small></p>
                            @else
                            <p class="text-success mb-0 mt-2"><small><i class="bi bi-check-circle"></i> All users verified</small></p>
                            @endif
                        </div>
                    </div>
                    @if($pendingVerifications > 0)
                    <a href="{{ route('users.pending') }}" class="btn btn-warning w-100 mt-2">
                        <i class="bi bi-arrow-right-circle"></i> Review Pending Users
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <div class="card stat-card h-100 clickable-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-info bg-opacity-10">
                                <i class="bi bi-people text-info"></i>
                            </div>
                            <span class="badge bg-info bg-opacity-10 text-info">System</span>
                        </div>
                        <h6 class="text-muted mb-2">Total Users</h6>
                        <h2 class="fw-bold mb-0 text-dark">{{ $totalUsers }}</h2>
                        <small class="text-info"><i class="bi bi-arrow-right-circle"></i> Manage users</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('documents.index') }}" class="text-decoration-none">
                <div class="card stat-card h-100 clickable-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-success bg-opacity-10">
                                <i class="bi bi-building text-success"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                        </div>
                        <h6 class="text-muted mb-2">Departments</h6>
                        <h2 class="fw-bold mb-0 text-dark">{{ $totalDepartments }}</h2>
                        <small class="text-success"><i class="bi bi-arrow-right-circle"></i> View by dept</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Document Status Distribution -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Document Status Distribution</h5>
                </div>
                <div class="card-body">
                    @foreach($documentsByStatus as $status => $count)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $status }}</span>
                            <span class="fw-bold">{{ $count }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $totalDocuments > 0 ? ($count / $totalDocuments) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Documents</h5>
                    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document #</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDocuments as $document)
                                <tr>
                                    <td>
                                        <a href="{{ route('documents.show', $document) }}" class="text-decoration-none">
                                            {{ $document->document_number }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ Str::limit($document->title, 30) }}
                                        @if($document->is_priority)
                                        <span class="badge badge-priority">PRIORITY</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Pending' ? 'warning' : 'info') }}">
                                            {{ $document->status }}
                                        </span>
                                    </td>
                                    <td><small>{{ $document->created_at->diffForHumans() }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No documents yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($pendingUsers->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-person-exclamation"></i> Pending User Verifications</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Role</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingUsers as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->department ? $user->department->name : 'N/A' }}</td>
                                    <td><span class="badge bg-secondary">{{ $user->roles->first() ? $user->roles->first()->name : 'No Role' }}</span></td>
                                    <td><small>{{ $user->created_at->diffForHumans() }}</small></td>
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
        </div>
    </div>
    @endif
</div>
@endsection

