@extends('layouts.app')

@section('title', 'Archived Documents')

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
    
    /* Title column - allow truncation but keep icons visible */
    .table tbody td:nth-child(2) {
        max-width: 250px;
        position: relative;
        line-height: 1.4;
    }
    
    /* Title cell wrapper for tooltip - truncate only the title text */
    .title-cell {
        position: relative;
        display: inline-flex;
        align-items: center;
        max-width: calc(100% - 25px);
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
    
    /* Ensure status icons are always visible */
    .table tbody td:nth-child(2) i {
        display: inline-block;
        flex-shrink: 0;
        margin-left: 4px;
        vertical-align: middle;
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
    
    /* Hover effect for rows - make rows clickable */
    .table tbody tr {
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
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
        .table tbody td:nth-child(2) .title-cell {
            max-width: calc(100% - 25px);
        }
        .table tbody td:nth-child(3) {
            max-width: 150px;
        }
    }
    
    @media (max-width: 992px) {
        .table tbody td:nth-child(2) {
            max-width: 150px;
        }
        .table tbody td:nth-child(2) .title-cell {
            max-width: calc(100% - 25px);
        }
        .table tbody td:nth-child(3) {
            max-width: 120px;
        }
    }
</style>
<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi bi-archive"></i> Archived Documents</h2>
        <p class="text-muted">View and manage archived documents</p>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('archive.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search archived documents..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="from_date" class="form-control" placeholder="From Date" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="to_date" class="form-control" placeholder="To Date" value="{{ request('to_date') }}">
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

    <!-- Archived Documents Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-archive"></i> Archived Documents 
                <span class="badge bg-secondary">{{ $archivedDocuments->total() }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 12%; white-space: nowrap;">DOCUMENT #</th>
                            <th style="width: 22%; white-space: nowrap;">TITLE</th>
                            <th class="text-center" style="width: 10%; white-space: nowrap;">TYPE</th>
                            <th class="text-center" style="width: 12%; white-space: nowrap;">CURRENT DEPARTMENT</th>
                            <th class="text-center" style="width: 12%; white-space: nowrap;">CREATED BY</th>
                            <th class="text-center" style="width: 14%; white-space: nowrap;">ARCHIVED DATE</th>
                            <th class="text-center" style="width: 18%; white-space: nowrap;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivedDocuments as $document)
                        <tr onclick="window.location.href='{{ route('archive.show', $document) }}'">
                            <td class="text-center"><strong>{{ $document->document_number }}</strong></td>
                            <td>
                                <span class="title-cell" data-full-title="{{ $document->title }}">
                                    <span class="title-cell-text">{{ $document->title }}</span>
                                </span>
                                @php
                                    // Get the status before archiving to show correct badge
                                    $preArchiveStatus = $document->getPreArchiveStatus();
                                    $displayStatus = $preArchiveStatus ?: $document->status;
                                @endphp
                                @if($displayStatus == 'Approved')
                                <i class="bi bi-check-circle-fill text-success" title="Approved" style="font-size: 0.9rem;"></i>
                                @endif
                                @if($displayStatus == 'Completed')
                                <i class="bi bi-check-circle-fill text-primary" title="Completed" style="font-size: 0.9rem;"></i>
                                @endif
                                @if($displayStatus == 'Rejected')
                                <i class="bi bi-x-circle-fill text-danger" title="Rejected" style="font-size: 0.9rem;"></i>
                                @endif
                            </td>
                            <td class="text-center"><span class="badge bg-secondary type-badge" title="{{ $document->document_type }}">{{ Str::limit($document->document_type, 10) }}</span></td>
                            <td class="text-center">{{ $document->department->code ?? 'N/A' }}</td>
                            <td class="text-center">{{ $document->creator->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if($document->archived_at)
                                    <small>{{ $document->archived_at->format('M d, Y') }}</small><br>
                                    <small class="text-muted">{{ $document->archived_at->diffForHumans() }}</small>
                                @else
                                    <small class="text-muted">N/A</small>
                                @endif
                            </td>
                            <td class="text-center" onclick="event.stopPropagation();">
                                <div class="d-flex gap-1 justify-content-center">
                                    @can('archive-documents')
                                    <form method="POST"
                                          action="{{ route('archive.restore', $document) }}"
                                          class="d-inline"
                                          data-swal-confirm="true"
                                          data-swal-title="Retrieve Document?"
                                          data-swal-text="Retrieve {{ $document->document_number }} - {{ Str::limit($document->title, 60) }} from archive?"
                                          data-swal-confirm-text="Yes, retrieve"
                                          data-swal-cancel-text="Cancel"
                                          data-swal-icon="question"
                                          data-swal-show-cancel-message="true"
                                          data-swal-cancel-title="Retrieval Cancelled"
                                          data-swal-cancel-text="Document {{ $document->document_number }} remained in archive.">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Retrieve" onclick="event.stopPropagation();">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('documents.report', ['document' => $document->id, 'format' => 'pdf']) }}" class="btn btn-sm btn-info" title="Generate Report" target="_blank">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                    @endcan
                                    @role('Administrator')
                                    <form method="POST"
                                          action="{{ route('archive.destroy', $document) }}"
                                          class="d-inline"
                                          data-swal-title="Permanently Delete Archived Document?"
                                          data-swal-text="Delete {{ $document->document_number }} - {{ Str::limit($document->title, 60) }} permanently? This cannot be undone."
                                          data-swal-confirm-text="Yes, delete permanently"
                                          data-swal-cancel-text="Cancel"
                                          data-swal-icon="warning"
                                          data-swal-show-cancel-message="true"
                                          data-swal-cancel-title="Deletion Cancelled"
                                          data-swal-cancel-text="Document {{ $document->document_number }} was not deleted.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Permanently Delete" onclick="event.stopPropagation();">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endrole
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-archive" style="font-size: 3rem;"></i>
                                <p class="mb-0">No archived documents found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $archivedDocuments->firstItem() ?? 0 }} to {{ $archivedDocuments->lastItem() ?? 0 }} of {{ $archivedDocuments->total() }} results
                </div>
                <nav aria-label="Archived document pagination">
                    <ul class="pagination mb-0">
                        {{-- Previous Page Link --}}
                        @if ($archivedDocuments->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $archivedDocuments->previousPageUrl() }}" rel="prev">Previous</a>
                            </li>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($archivedDocuments->getUrlRange(1, $archivedDocuments->lastPage()) as $page => $url)
                            @if ($page == $archivedDocuments->currentPage())
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
                        @if ($archivedDocuments->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $archivedDocuments->nextPageUrl() }}" rel="next">Next</a>
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
