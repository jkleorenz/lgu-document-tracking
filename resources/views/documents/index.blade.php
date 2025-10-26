@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-file-earmark-text"></i> Documents</h2>
        @role('Administrator')
        <a href="{{ route('documents.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Document
        </a>
        @endrole
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('documents.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search documents..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
                            <option value="Under Review" {{ request('status') == 'Under Review' ? 'selected' : '' }}>Under Review</option>
                            <option value="Forwarded" {{ request('status') == 'Forwarded' ? 'selected' : '' }}>Forwarded</option>
                            <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    @role('Administrator')
                    <div class="col-md-2">
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endrole
                    <div class="col-md-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="priority" id="priority" value="1" {{ request('priority') ? 'checked' : '' }}>
                            <label class="form-check-label" for="priority">
                                Priority Only
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Document #</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Current Handler</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                        <tr class="{{ $document->is_priority ? 'table-warning' : '' }}">
                            <td>
                                <strong>{{ $document->document_number }}</strong>
                                @if($document->is_priority)
                                <br><span class="badge badge-priority">PRIORITY</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($document->title, 50) }}</td>
                            <td><span class="badge bg-secondary">{{ $document->document_type }}</span></td>
                            <td>{{ $document->department ? $document->department->code : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Received' ? 'success' : ($document->status == 'Pending' ? 'warning' : ($document->status == 'Rejected' ? 'danger' : 'info'))) }}">
                                    {{ $document->status }}
                                </span>
                            </td>
                            <td>{{ $document->creator ? $document->creator->name : 'Unknown' }}</td>
                            <td>
                                @if($document->currentHandler)
                                {{ $document->currentHandler->name }}
                                @else
                                <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td><small>{{ $document->created_at->format('M d, Y') }}</small></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('manage-documents')
                                    @if($document->created_by == auth()->id() || auth()->user()->hasRole('Administrator'))
                                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                    @endcan
                                    <a href="{{ route('documents.print-qr', $document) }}" class="btn btn-sm btn-secondary" title="Print QR" target="_blank">
                                        <i class="bi bi-qr-code"></i>
                                    </a>
                                    @role('Administrator|Department Head')
                                    @if($document->status != 'Archived')
                                    <form method="POST" action="{{ route('documents.archive', $document) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-dark" title="Archive" onclick="return confirm('Archive this document?')">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @endrole
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mb-0">No documents found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $documents->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

