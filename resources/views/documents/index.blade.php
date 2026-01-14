@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<style>
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

    /* ============================================
       UI/UX IMPROVEMENTS - Document Table
       ============================================ */
    
    /* Table container with sticky actions column */
    .table-responsive {
        position: relative;
    }
    
    /* Consistent row height - prevents long titles from making rows too tall */
    .table tbody tr {
        height: auto;
        min-height: 60px;
    }
    
    .table tbody td {
        vertical-align: middle;
        padding: 0.75rem 0.5rem;
    }
    
    /* Title column - truncate with ellipsis and show tooltip on hover */
    .table tbody td:nth-child(2) {
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        position: relative;
        line-height: 1.4;
    }
    
    /* Title cell wrapper for tooltip */
    .title-cell {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }
    
    /* Sticky Actions Column - Always visible */
    .table thead th:last-child,
    .table tbody td:last-child {
        position: sticky;
        right: 0;
        background-color: inherit;
        z-index: 10;
        box-shadow: -2px 0 4px rgba(0, 0, 0, 0.1);
        min-width: 140px;
    }
    
    /* Ensure header matches body for sticky column */
    .table thead th:last-child {
        background-color: #f8f9fa;
        z-index: 11;
    }
    
    /* Priority rows should also have sticky background */
    .table tbody tr.table-warning td:last-child {
        background-color: #fff3cd;
    }
    
    /* Hover effect for rows - keep existing functionality */
    .table tbody tr {
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .table tbody tr.table-warning:hover {
        background-color: #ffeaa7;
    }
    
    /* Prevent action buttons from triggering row click */
    .table tbody tr td:last-child,
    .table tbody tr td:last-child * {
        cursor: default;
        pointer-events: auto;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .table tbody td:nth-child(2) {
            max-width: 200px;
        }
    }
    
    @media (max-width: 992px) {
        .table tbody td:nth-child(2) {
            max-width: 150px;
        }
    }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-file-earmark-text"></i> Documents</h2>
        @role('Administrator|Mayor|LGU Staff|Department Head')
        <a href="{{ route('documents.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Generate Document QR
        </a>
        @endrole
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-funnel"></i> Filters
                @if(request()->hasAny(['search', 'status', 'department', 'priority']))
                <span class="badge bg-info ms-2">Active</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('documents.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search documents..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Current Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
                            <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="Return" {{ request('status') == 'Return' ? 'selected' : '' }}>Returned</option>
                        </select>
                    </div>
                    @role('Administrator|Mayor')
                    <div class="col-md-2">
                        <label class="form-label small">Department</label>
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
                        <label class="form-label small">Priority</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="priority" id="priority" value="1" {{ request('priority') ? 'checked' : '' }}>
                            <label class="form-check-label" for="priority">
                                Priority Only
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Summary -->
    @if(request()->hasAny(['search', 'status', 'department', 'priority']))
    <div class="alert alert-info">
        <strong><i class="bi bi-info-circle"></i> Showing filtered results:</strong>
        @if(request('search'))
            <span class="badge bg-primary">Search: "{{ request('search') }}"</span>
        @endif
        @if(request('status'))
            <span class="badge bg-primary">Status: {{ request('status') === 'for_review' ? 'For Review' : request('status') }}</span>
        @endif
        @if(request('department'))
            @php
                $dept = $departments->find(request('department'));
            @endphp
            @if($dept)
                <span class="badge bg-primary">Department: {{ $dept->name }}</span>
            @endif
        @endif
        @if(request('priority'))
            <span class="badge bg-warning">Priority Only</span>
        @endif
        <span class="ms-2">{{ $documents->total() }} {{ Str::plural('document', $documents->total()) }} found</span>
    </div>
    @endif

    <!-- Documents Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-table"></i> Documents List</h6>
            <span class="badge bg-secondary">Total: {{ $documents->total() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 12%; white-space: nowrap;">DOCUMENT #</th>
                            <th style="width: 18%; white-space: nowrap;">TITLE</th>
                            <th class="text-center" style="width: 8%; white-space: nowrap;">TYPE</th>
                            <th class="text-center" style="width: 10%; white-space: nowrap;">CURRENT DEPARTMENT</th>
                            <th class="text-center" style="width: 10%; white-space: nowrap;">CURRENT STATUS</th>
                            <th class="text-center" style="width: 8%; white-space: nowrap;">DATE</th>
                            <th class="text-center" style="width: 12%; white-space: nowrap;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                        <tr class="{{ $document->is_priority ? 'table-warning' : '' }}" onclick="window.location.href='{{ route('documents.show', $document) }}'">
                            <td class="text-center">
                                <strong>{{ $document->document_number }}</strong>
                                @if($document->is_priority)
                                <br><span class="badge badge-priority">PRIORITY</span>
                                @endif
                            </td>
                            <td>
                                <span class="title-cell" title="{{ $document->title }}">
                                    {{ $document->title }}
                                </span>
                                @php
                                    // For archived documents, check pre-archive status for icon display
                                    $iconStatus = $document->status;
                                    if ($document->status === 'Archived') {
                                        $preArchiveStatus = $document->getPreArchiveStatus();
                                        if ($preArchiveStatus && ($preArchiveStatus === 'Rejected' || $preArchiveStatus === 'Approved' || $preArchiveStatus === 'Completed')) {
                                            $iconStatus = $preArchiveStatus;
                                        }
                                    }
                                @endphp
                                @if($iconStatus == 'Approved')
                                <i class="bi bi-check-circle-fill text-success" title="Approved" style="font-size: 0.9rem;"></i>
                                @endif
                                @if($iconStatus == 'Completed')
                                <i class="bi bi-check-circle-fill text-primary" title="Completed" style="font-size: 0.9rem;"></i>
                                @endif
                                @if($iconStatus == 'Rejected')
                                <i class="bi bi-x-circle-fill text-danger" title="Rejected" style="font-size: 0.9rem;"></i>
                                @endif
                            </td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $document->document_type }}</span></td>
                            <td class="text-center">
                                @if(in_array($document->status, ['Forwarded', 'Pending']))
                                <span class="text-muted">N/A</span>
                                @else
                                {{ $document->department ? $document->department->code : 'N/A' }}
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    // For archived documents, get the pre-archive status for display
                                    $displayStatus = $document->status;
                                    $preArchiveStatus = null;
                                    $isArchived = false;
                                    if ($document->status === 'Archived') {
                                        $preArchiveStatus = $document->getPreArchiveStatus();
                                        if ($preArchiveStatus && ($preArchiveStatus === 'Rejected' || $preArchiveStatus === 'Approved' || $preArchiveStatus === 'Completed')) {
                                            $displayStatus = $preArchiveStatus;
                                            $isArchived = true;
                                        }
                                    }
                                    // Also check if document is completed and archived (has archived_at set)
                                    if ($document->status === 'Completed' && $document->isArchived()) {
                                        $isArchived = true;
                                    }
                                @endphp
                                <span class="badge bg-{{ $displayStatus == 'Approved' ? 'success' : ($displayStatus == 'Completed' ? 'primary' : ($displayStatus == 'Return' ? 'danger' : ($displayStatus == 'Received' ? 'success' : ($displayStatus == 'Pending' ? 'warning' : ($displayStatus == 'Rejected' ? 'danger' : 'info'))))) }}">
                                    @if($displayStatus == 'Completed' && $isArchived)
                                        Completed (Archived)
                                    @elseif($displayStatus == 'Approved' && $isArchived)
                                        Approved (Archived)
                                    @else
                                        {{ $displayStatus }}
                                    @endif
                                </span>
                            </td>
                            <td class="text-center"><small>{{ $document->created_at->format('M d, Y') }}</small></td>
                            <td class="text-center" onclick="event.stopPropagation();">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('manage-documents')
                                    @if($document->created_by == auth()->id() || auth()->user()->hasAnyRole(['Administrator', 'Mayor']))
                                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                    @endcan
                                    <a href="{{ route('documents.print-qr', $document) }}" class="btn btn-sm btn-secondary" title="Print QR" target="_blank">
                                        <i class="bi bi-qr-code"></i>
                                    </a>
                                    @can('archive-documents')
                                    @if($document->status != 'Archived')
                                    <form method="POST" action="{{ route('documents.archive', $document) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-dark" title="Archive" onclick="event.stopPropagation(); return confirm('Archive this document?')">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mb-0">No documents found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $documents->firstItem() ?? 0 }} to {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }} results
                </div>
                <nav aria-label="Document pagination">
                    <ul class="pagination mb-0">
                        {{-- Previous Page Link --}}
                        @if ($documents->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $documents->previousPageUrl() }}" rel="prev">Previous</a>
                            </li>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($documents->getUrlRange(1, $documents->lastPage()) as $page => $url)
                            @if ($page == $documents->currentPage())
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
                        @if ($documents->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $documents->nextPageUrl() }}" rel="next">Next</a>
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

