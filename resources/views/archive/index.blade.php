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
        display: inline-block;
        max-width: calc(100% - 25px);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
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
                                <span class="title-cell" title="{{ $document->title }}">
                                    {{ $document->title }}
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
                                    <form method="POST" action="{{ route('archive.restore', $document) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Retrieve" onclick="event.stopPropagation(); return confirm('Retrieve this document from archive?')">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('documents.report', ['document' => $document->id, 'format' => 'pdf']) }}" class="btn btn-sm btn-info" title="Generate Report" target="_blank">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                    @endcan
                                    @role('Administrator')
                                    <form method="POST" action="{{ route('archive.destroy', $document) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Permanently Delete" onclick="event.stopPropagation(); return confirm('⚠️ PERMANENTLY DELETE this document?\n\nThis action CANNOT be undone!\n\nDocument: {{ $document->document_number }}')">
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
