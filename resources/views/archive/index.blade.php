@extends('layouts.app')

@section('title', 'Archived Documents')

@section('content')
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
                        <tr>
                            <td class="text-center"><strong>{{ $document->document_number }}</strong></td>
                            <td>
                                {{ Str::limit($document->title, 50) }}
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
                            <td class="text-center"><span class="badge bg-secondary">{{ $document->document_type }}</span></td>
                            <td class="text-center">{{ $document->department->code }}</td>
                            <td class="text-center">{{ $document->creator->name }}</td>
                            <td class="text-center">
                                @if($document->archived_at)
                                    <small>{{ $document->archived_at->format('M d, Y') }}</small><br>
                                    <small class="text-muted">{{ $document->archived_at->diffForHumans() }}</small>
                                @else
                                    <small class="text-muted">N/A</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('archive.show', $document) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('archive-documents')
                                    <form method="POST" action="{{ route('archive.restore', $document) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Retrieve" onclick="return confirm('Retrieve this document from archive?')">
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
                                        <button type="submit" class="btn btn-sm btn-danger" title="Permanently Delete" onclick="return confirm('⚠️ PERMANENTLY DELETE this document?\n\nThis action CANNOT be undone!\n\nDocument: {{ $document->document_number }}')">
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

            <div class="mt-3">
                {{ $archivedDocuments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

