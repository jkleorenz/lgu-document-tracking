@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<style>
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
        position: relative;
        line-height: 1.4;
    }
    
    /* Title cell wrapper for tooltip */
    .title-cell {
        position: relative;
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        vertical-align: middle;
        gap: 0.25rem;
    }

    .title-cell-text {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    .title-cell::after,
    .title-cell::before {
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .title-cell::after {
        content: attr(data-full-title);
        position: absolute;
        left: 0;
        bottom: calc(100% + 8px);
        background: #1f2328;
        color: #fff;
        padding: 0.4rem 0.6rem;
        border-radius: 0.45rem;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.18);
        max-width: 360px;
        white-space: normal;
        line-height: 1.3;
        z-index: 20;
        transform: translateY(-4px);
    }

    .title-cell::before {
        content: '';
        position: absolute;
        left: 12px;
        bottom: calc(100% + 4px);
        border: 6px solid transparent;
        border-top-color: #1f2328;
        z-index: 19;
        transform: translateY(-4px);
    }

    .title-cell:hover::after,
    .title-cell:hover::before,
    .title-cell:focus-within::after,
    .title-cell:focus-within::before {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Type column - truncate with ellipsis and show tooltip on hover */
    .table tbody td:nth-child(3) {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        position: relative;
    }
    
    /* Type badge wrapper for tooltip */
    .type-badge {
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
        .table tbody td:nth-child(3) {
            max-width: 150px;
        }
    }
    
    @media (max-width: 992px) {
        .table tbody td:nth-child(2) {
            max-width: 150px;
        }
        .table tbody td:nth-child(3) {
            max-width: 120px;
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
                                <span class="title-cell" data-full-title="{{ $document->title }}">
                                    <span class="title-cell-text">{{ $document->title }}</span>
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
                            <td class="text-center"><span class="badge bg-secondary type-badge" title="{{ $document->document_type }}">{{ Str::limit($document->document_type, 10) }}</span></td>
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
                            <td class="text-end" onclick="event.stopPropagation();">
                                <div class="d-flex gap-1 justify-content-end">
                                    @php
                                        $currentUser = auth()->user();
                                        $canEdit = $currentUser && $currentUser->can('manage-documents') && (
                                            $document->created_by == $currentUser->id ||
                                            $currentUser->hasAnyRole(['Administrator', 'Mayor'])
                                        );
                                        $canArchive = $currentUser && $currentUser->can('archive-documents') && !$document->isArchived();
                                        $hasManagePermission = $currentUser && $currentUser->can('manage-documents');
                                        $editTooltip = 'Edit document';
                                        if (!$canEdit) {
                                            $editTooltip = $hasManagePermission
                                                ? 'Only the creator, Administrator, or Mayor can edit this document.'
                                                : 'You do not have permission to edit documents.';
                                        }

                                        $alreadyArchived = $document->isArchived();
                                        $hasArchivePermission = $currentUser && $currentUser->can('archive-documents');
                                        $canArchive = $hasArchivePermission && !$alreadyArchived;
                                        $archiveTooltip = $canArchive
                                            ? 'Archive document'
                                            : ($alreadyArchived
                                                ? 'Document is already archived.'
                                                : 'You do not have permission to archive documents.');
                                    @endphp

                                    <a href="{{ $canEdit ? route('documents.edit', $document) : 'javascript:void(0);' }}"
                                       class="btn btn-sm btn-warning action-btn {{ $canEdit ? '' : 'action-btn--disabled' }}"
                                       title="{{ $editTooltip }}"
                                       @unless($canEdit) aria-disabled="true" tabindex="-1" @endunless>
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <a href="{{ route('documents.print-qr', $document) }}"
                                       class="btn btn-sm btn-secondary action-btn"
                                       title="Print QR" target="_blank">
                                        <i class="bi bi-qr-code"></i>
                                    </a>

                                    <form method="POST"
                                          action="{{ route('documents.archive', $document) }}"
                                          class="d-inline"
                                          data-swal-confirm="true"
                                          data-swal-title="Archive Document?"
                                          data-swal-text="Archive {{ $document->document_number }} - {{ Str::limit($document->title, 60) }}?"
                                          data-swal-confirm-text="Yes, archive"
                                          data-swal-cancel-text="Cancel"
                                          data-swal-icon="warning"
                                          data-swal-show-cancel-message="true"
                                          data-swal-cancel-title="Archive Cancelled"
                                          data-swal-cancel-text="Document {{ $document->document_number }} was not archived.">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm btn-dark action-btn {{ $canArchive ? '' : 'action-btn--disabled' }} {{ $alreadyArchived ? 'action-btn--archived' : '' }}"
                                                title="{{ $archiveTooltip }}"
                                                onclick="event.stopPropagation();"
                                                @unless($canArchive) disabled aria-disabled="true" @endunless>
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
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

            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $documents->firstItem() ?? 0 }} to {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }} results
                </div>
                <div class="pagination-wrap">
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
</div>
@endsection
