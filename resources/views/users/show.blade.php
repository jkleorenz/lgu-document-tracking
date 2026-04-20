@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<style>
    /* ============================================
       UI/UX IMPROVEMENTS - Table Design
       ============================================ */
    .table-responsive {
        position: relative;
    }
    
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
    
    .table tbody tr {
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Card styling */
    .info-label {
        font-weight: 600;
        width: 150px;
        color: #495057;
    }
</style>

<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">{{ $user->name }}</li>
            </ol>
        </nav>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary">
                    {{ $user->email }}
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-0">
                            <i class="bi bi-person-circle me-2"></i>{{ $user->name }}
                        </h2>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('users.password.reset', $user) }}" class="btn btn-info">
                            <i class="bi bi-key"></i> Reset Password
                        </a>
                        <a href="{{ route('users.password.view', $user) }}" class="btn btn-secondary">
                            <i class="bi bi-eye"></i> View Password
                        </a>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit User
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- (1) User Information Card -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle text-primary me-2"></i>User Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="info-label">Full Name:</td>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Email Address:</td>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Phone Number:</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Department:</td>
                            <td>{{ $user->department->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="info-label">Current Role:</td>
                            <td><span class="badge bg-primary">{{ $user->roles->first()->name ?? 'No Role' }}</span></td>
                        </tr>
                        <tr>
                            <td class="info-label">Account Status:</td>
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
                            <td class="info-label">Date Joined:</td>
                            <td>{{ $user->created_at->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Last Login:</td>
                            <td>{{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- (2) Statistics Card -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold"><i class="bi bi-graph-up text-primary me-2"></i>Statistics Summary</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4 border-end">
                    <div class="p-3">
                        <small class="text-muted d-block mb-1">Total Documents Created</small>
                        <h2 class="fw-bold mb-0 text-primary">{{ $createdDocuments->total() }}</h2>
                    </div>
                </div>
                <div class="col-md-4 border-end">
                    <div class="p-3">
                        <small class="text-muted d-block mb-1">Currently Handling</small>
                        <h2 class="fw-bold mb-0 text-success">{{ $handlingDocuments->total() }}</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3">
                        <small class="text-muted d-block mb-1">Status Updates Made</small>
                        <h2 class="fw-bold mb-0 text-info">{{ $user->statusLogs->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- (3) Created Documents Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text text-primary me-2"></i>Created Documents</h5>
            <span class="badge bg-secondary">Total: {{ $createdDocuments->total() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 15%;">DOCUMENT #</th>
                            <th style="width: 35%;">TITLE</th>
                            <th class="text-center" style="width: 20%;">DEPARTMENT</th>
                            <th class="text-center" style="width: 15%;">CURRENT STATUS</th>
                            <th class="text-center" style="width: 15%;">DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($createdDocuments as $document)
                        <tr class="clickable-row" data-href="{{ route('documents.show', $document) }}">
                            <td class="text-center"><strong>{{ $document->document_number }}</strong></td>
                            <td>
                                <span class="title-cell" data-full-title="{{ $document->title }}">
                                    <span class="title-cell-text">{{ $document->title }}</span>
                                </span>
                                @if($document->status == 'Approved')
                                <i class="bi bi-check-circle-fill text-success" title="Approved" style="font-size: 0.9rem;"></i>
                                @endif
                                @if($document->status == 'Rejected')
                                <i class="bi bi-x-circle-fill text-danger" title="Rejected" style="font-size: 0.9rem;"></i>
                                @endif
                            </td>
                            <td class="text-center">{{ $document->department->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Pending' ? 'warning' : ($document->status == 'Rejected' ? 'danger' : 'info')) }}">
                                    {{ $document->status }}
                                </span>
                            </td>
                            <td class="text-center"><small>{{ $document->created_at->format('M d, Y') }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No documents created</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination for Created Documents -->
            @if($createdDocuments->hasPages())
            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $createdDocuments->firstItem() ?? 0 }} to {{ $createdDocuments->lastItem() ?? 0 }} of {{ $createdDocuments->total() }} results
                </div>
                <div class="pagination-wrap">
                    <nav aria-label="Created documents pagination">
                        <ul class="pagination mb-0">
                        {{-- Previous Page Link --}}
                        @if ($createdDocuments->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $createdDocuments->appends(['handling_page' => request('handling_page')])->previousPageUrl() }}" rel="prev">Previous</a>
                            </li>
                        @endif

                        {{-- Custom Pagination Elements with 1-7 pages limit --}}
                        @php
                            $currentPage = $createdDocuments->currentPage();
                            $lastPage = $createdDocuments->lastPage();
                            $maxPages = 7;
                            
                            $customElements = [];
                            if ($lastPage <= $maxPages) {
                                for ($i = 1; $i <= $lastPage; $i++) {
                                    $customElements[$i] = $createdDocuments->appends(['handling_page' => request('handling_page')])->url($i);
                                }
                            } else {
                                if ($currentPage <= 4) {
                                    for ($i = 1; $i <= 5; $i++) {
                                        $customElements[$i] = $createdDocuments->appends(['handling_page' => request('handling_page')])->url($i);
                                    }
                                    $customElements['...'] = 'ellipsis';
                                    $customElements[$lastPage] = $createdDocuments->appends(['handling_page' => request('handling_page')])->url($lastPage);
                                } elseif ($currentPage >= $lastPage - 3) {
                                    $customElements[1] = $createdDocuments->appends(['handling_page' => request('handling_page')])->url(1);
                                    $customElements['...'] = 'ellipsis';
                                    for ($i = $lastPage - 4; $i <= $lastPage; $i++) {
                                        $customElements[$i] = $createdDocuments->appends(['handling_page' => request('handling_page')])->url($i);
                                    }
                                } else {
                                    $customElements[1] = $createdDocuments->appends(['handling_page' => request('handling_page')])->url(1);
                                    $customElements['...'] = 'ellipsis';
                                    for ($i = $currentPage - 1; $i <= $currentPage + 1; $i++) {
                                        $customElements[$i] = $createdDocuments->appends(['handling_page' => request('handling_page')])->url($i);
                                    }
                                    $customElements['...'] = 'ellipsis';
                                    $customElements[$lastPage] = $createdDocuments->appends(['handling_page' => request('handling_page')])->url($lastPage);
                                }
                            }
                        @endphp
                        
                        @foreach ($customElements as $page => $url)
                            @if ($page === '...')
                                <li class="page-item disabled" aria-disabled="true"><span class="page-link">...</span></li>
                            @elseif ($page == $currentPage)
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
                        @if ($createdDocuments->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $createdDocuments->appends(['handling_page' => request('handling_page')])->nextPageUrl() }}" rel="next">Next</a>
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
            @endif
        </div>
    </div>

    <!-- (4) Currently Handling Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0 fw-bold"><i class="bi bi-briefcase text-primary me-2"></i>Currently Handling</h5>
            <span class="badge bg-secondary">Total: {{ $handlingDocuments->total() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 15%;">DOCUMENT #</th>
                            <th style="width: 35%;">TITLE</th>
                            <th class="text-center" style="width: 20%;">DEPARTMENT</th>
                            <th class="text-center" style="width: 15%;">CURRENT STATUS</th>
                            <th class="text-center" style="width: 15%;">DATE ASSIGNED</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($handlingDocuments as $document)
                        <tr class="clickable-row" data-href="{{ route('documents.show', $document) }}">
                            <td class="text-center"><strong>{{ $document->document_number }}</strong></td>
                            <td>
                                <span class="title-cell" data-full-title="{{ $document->title }}">
                                    <span class="title-cell-text">{{ $document->title }}</span>
                                </span>
                                @if($document->status == 'Approved')
                                <i class="bi bi-check-circle-fill text-success" title="Approved" style="font-size: 0.9rem;"></i>
                                @endif
                                @if($document->status == 'Rejected')
                                <i class="bi bi-x-circle-fill text-danger" title="Rejected" style="font-size: 0.9rem;"></i>
                                @endif
                            </td>
                            <td class="text-center">{{ $document->department->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Received' ? 'success' : ($document->status == 'Pending' ? 'warning' : ($document->status == 'Rejected' ? 'danger' : 'info'))) }}">
                                    {{ $document->status }}
                                </span>
                            </td>
                            <td class="text-center"><small>{{ $document->updated_at->format('M d, Y') }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No documents being handled</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination for Currently Handling -->
            @if($handlingDocuments->hasPages())
            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $handlingDocuments->firstItem() ?? 0 }} to {{ $handlingDocuments->lastItem() ?? 0 }} of {{ $handlingDocuments->total() }} results
                </div>
                <div class="pagination-wrap">
                    <nav aria-label="Currently handling pagination">
                        <ul class="pagination mb-0">
                        {{-- Previous Page Link --}}
                        @if ($handlingDocuments->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $handlingDocuments->appends(['created_page' => request('created_page')])->previousPageUrl() }}" rel="prev">Previous</a>
                            </li>
                        @endif

                        {{-- Custom Pagination Elements with 1-7 pages limit --}}
                        @php
                            $currentPage = $handlingDocuments->currentPage();
                            $lastPage = $handlingDocuments->lastPage();
                            $maxPages = 7;
                            
                            $customElements = [];
                            if ($lastPage <= $maxPages) {
                                for ($i = 1; $i <= $lastPage; $i++) {
                                    $customElements[$i] = $handlingDocuments->appends(['created_page' => request('created_page')])->url($i);
                                }
                            } else {
                                if ($currentPage <= 4) {
                                    for ($i = 1; $i <= 5; $i++) {
                                        $customElements[$i] = $handlingDocuments->appends(['created_page' => request('created_page')])->url($i);
                                    }
                                    $customElements['...'] = 'ellipsis';
                                    $customElements[$lastPage] = $handlingDocuments->appends(['created_page' => request('created_page')])->url($lastPage);
                                } elseif ($currentPage >= $lastPage - 3) {
                                    $customElements[1] = $handlingDocuments->appends(['created_page' => request('created_page')])->url(1);
                                    $customElements['...'] = 'ellipsis';
                                    for ($i = $lastPage - 4; $i <= $lastPage; $i++) {
                                        $customElements[$i] = $handlingDocuments->appends(['created_page' => request('created_page')])->url($i);
                                    }
                                } else {
                                    $customElements[1] = $handlingDocuments->appends(['created_page' => request('created_page')])->url(1);
                                    $customElements['...'] = 'ellipsis';
                                    for ($i = $currentPage - 1; $i <= $currentPage + 1; $i++) {
                                        $customElements[$i] = $handlingDocuments->appends(['created_page' => request('created_page')])->url($i);
                                    }
                                    $customElements['...'] = 'ellipsis';
                                    $customElements[$lastPage] = $handlingDocuments->appends(['created_page' => request('created_page')])->url($lastPage);
                                }
                            }
                        @endphp
                        
                        @foreach ($customElements as $page => $url)
                            @if ($page === '...')
                                <li class="page-item disabled" aria-disabled="true"><span class="page-link">...</span></li>
                            @elseif ($page == $currentPage)
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
                        @if ($handlingDocuments->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $handlingDocuments->appends(['created_page' => request('created_page')])->nextPageUrl() }}" rel="next">Next</a>
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
            @endif
        </div>
    </div>
</div>
@endsection

