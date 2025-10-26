@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Staff Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <a href="{{ route('scan.index') }}" class="btn btn-primary">
            <i class="bi bi-qr-code-scan"></i> Scan QR Code
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <a href="{{ route('documents.index') }}" class="text-decoration-none">
                <div class="card stat-card clickable-card" style="border-left-color: #0d6efd;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">My Documents</h6>
                                <h2 class="fw-bold mb-0 text-dark">{{ $myDocuments }}</h2>
                                <small class="text-primary"><i class="bi bi-arrow-right-circle"></i> View all</small>
                            </div>
                            <div class="text-primary" style="font-size: 2.5rem;">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('documents.index', ['status' => 'Pending']) }}" class="text-decoration-none">
                <div class="card stat-card clickable-card" style="border-left-color: #ffc107;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Pending</h6>
                                <h2 class="fw-bold mb-0 text-dark">{{ $pendingDocuments }}</h2>
                                <small class="text-warning"><i class="bi bi-arrow-right-circle"></i> View pending</small>
                            </div>
                            <div class="text-warning" style="font-size: 2.5rem;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('documents.index', ['status' => 'Approved']) }}" class="text-decoration-none">
                <div class="card stat-card clickable-card" style="border-left-color: #198754;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Approved</h6>
                                <h2 class="fw-bold mb-0 text-dark">{{ $approvedDocuments }}</h2>
                                <small class="text-success"><i class="bi bi-arrow-right-circle"></i> View approved</small>
                            </div>
                            <div class="text-success" style="font-size: 2.5rem;">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('scan.index') }}" class="quick-action-btn">
                                <div class="quick-action-icon bg-primary bg-opacity-10">
                                    <i class="bi bi-qr-code-scan text-primary"></i>
                                </div>
                                <span class="text-primary">Scan QR Code</span>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('documents.index') }}" class="quick-action-btn">
                                <div class="quick-action-icon bg-info bg-opacity-10">
                                    <i class="bi bi-list-ul text-info"></i>
                                </div>
                                <span class="text-info">View Documents</span>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('notifications.index') }}" class="quick-action-btn">
                                <div class="quick-action-icon bg-warning bg-opacity-10">
                                    <i class="bi bi-bell text-warning"></i>
                                </div>
                                <span class="text-warning">Notifications</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Documents -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> My Recent Documents</h5>
                    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document Number</th>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Handler</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDocuments as $document)
                                <tr>
                                    <td>
                                        <strong>{{ $document->document_number }}</strong>
                                        @if($document->is_priority)
                                        <br><span class="badge badge-priority">PRIORITY</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($document->title, 40) }}</td>
                                    <td>{{ $document->department ? $document->department->name : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Pending' ? 'warning' : ($document->status == 'Rejected' ? 'danger' : 'info')) }}">
                                            {{ $document->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($document->currentHandler)
                                        {{ $document->currentHandler->name }}
                                        @else
                                        <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $document->created_at->format('M d, Y') }}</small></td>
                                    <td>
                                        <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mb-0">No documents created yet</p>
                                        <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm mt-2">
                                            Create Your First Document
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

