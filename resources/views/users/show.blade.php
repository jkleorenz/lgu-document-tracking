@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">{{ $user->name }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 class="fw-bold"><i class="bi bi-person-circle"></i> {{ $user->name }}</h2>
                <p class="text-muted">{{ $user->email }}</p>
            </div>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit User
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Name:</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td>{{ $user->department->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td><span class="badge bg-primary">{{ $user->roles->first()->name ?? 'No Role' }}</span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($user->status == 'verified')
                                <span class="badge bg-success">Verified</span>
                                @elseif($user->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                                @else
                                <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Joined:</th>
                            <td>{{ $user->created_at->format('F d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Documents Created</small>
                        <h3>{{ $user->createdDocuments->count() }}</h3>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Currently Handling</small>
                        <h3>{{ $handlingDocuments->count() }}</h3>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted">Status Updates Made</small>
                        <h3>{{ $user->statusLogs->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Created Documents</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Document #</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->createdDocuments->take(10) as $document)
                                <tr>
                                    <td>
                                        <a href="{{ route('documents.show', $document) }}">
                                            {{ $document->document_number }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ Str::limit($document->title, 50) }}
                                        @if($document->status == 'Approved')
                                        <i class="bi bi-check-circle-fill text-success" title="Approved" style="font-size: 0.85rem;"></i>
                                        @endif
                                        @if($document->status == 'Rejected')
                                        <i class="bi bi-x-circle-fill text-danger" title="Rejected" style="font-size: 0.85rem;"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Pending' ? 'warning' : ($document->status == 'Rejected' ? 'danger' : 'info')) }}">
                                            {{ $document->status }}
                                        </span>
                                    </td>
                                    <td><small>{{ $document->created_at->format('M d, Y') }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No documents created</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-briefcase"></i> Currently Handling</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Document #</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date Assigned</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($handlingDocuments as $document)
                                <tr>
                                    <td>
                                        <a href="{{ route('documents.show', $document) }}">
                                            {{ $document->document_number }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ Str::limit($document->title, 50) }}
                                        @if($document->status == 'Approved')
                                        <i class="bi bi-check-circle-fill text-success" title="Approved" style="font-size: 0.85rem;"></i>
                                        @endif
                                        @if($document->status == 'Rejected')
                                        <i class="bi bi-x-circle-fill text-danger" title="Rejected" style="font-size: 0.85rem;"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Received' ? 'success' : ($document->status == 'Pending' ? 'warning' : ($document->status == 'Rejected' ? 'danger' : 'info'))) }}">
                                            {{ $document->status }}
                                        </span>
                                    </td>
                                    <td><small>{{ $document->updated_at->format('M d, Y') }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No documents being handled</td>
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

