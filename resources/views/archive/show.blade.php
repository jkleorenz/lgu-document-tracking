@extends('layouts.app')

@section('title', 'Archived Document')

@section('content')
<style>
    .btn-archive-action {
        min-width: 180px;
        padding: 10px 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.95rem;
        font-weight: 500;
    }
    
    .btn-archive-action i {
        font-size: 1.1rem;
    }
</style>

<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('archive.index') }}">Archive</a></li>
                <li class="breadcrumb-item active">{{ $document->document_number }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 class="fw-bold">
                    <i class="bi bi-archive"></i> {{ $document->title }}
                    @if($document->status == 'Approved')
                    <i class="bi bi-check-circle-fill text-success ms-2" title="Approved"></i>
                    @endif
                </h2>
                <p class="text-muted">{{ $document->document_number }} <span class="badge bg-dark">ARCHIVED</span></p>
            </div>
            <div class="d-flex gap-2">
                @can('archive-documents')
                <form method="POST" action="{{ route('archive.restore', $document) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-archive-action" onclick="return confirm('Restore this document from archive?')">
                        <i class="bi bi-arrow-counterclockwise"></i> Restore Document
                    </button>
                </form>
                @endcan
                @role('Administrator')
                <form method="POST" action="{{ route('archive.destroy', $document) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-archive-action" onclick="return confirm('⚠️ PERMANENTLY DELETE this document?\n\nThis action CANNOT be undone!\n\nDocument: {{ $document->document_number }}')">
                        <i class="bi bi-trash"></i> Delete Permanently
                    </button>
                </form>
                @endrole
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="alert alert-dark">
                <i class="bi bi-archive"></i> This document was archived on {{ $document->archived_at->format('F d, Y h:i A') }}
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Document Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Document Number:</th>
                            <td><strong>{{ $document->document_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Title:</th>
                            <td>{{ $document->title }}</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td><span class="badge bg-secondary">{{ $document->document_type }}</span></td>
                        </tr>
                        <tr>
                            <th>Final Status:</th>
                            <td>
                                <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : 'secondary' }}">
                                    {{ $document->status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td>{{ $document->department ? $document->department->name : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td>{{ $document->creator ? $document->creator->name : 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <th>Created Date:</th>
                            <td>{{ $document->created_at->format('F d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Archived Date:</th>
                            <td>{{ $document->archived_at->format('F d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $document->description ?? 'No description provided' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Complete Document History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($document->statusLogs as $log)
                        <div class="d-flex mb-3">
                            <div class="text-muted me-3" style="min-width: 150px;">
                                <small>{{ $log->action_date->format('M d, Y') }}<br>{{ $log->action_date->format('h:i A') }}</small>
                            </div>
                            <div class="border-start border-2 ps-3 flex-grow-1">
                                <div class="mb-1">
                                    <span class="badge bg-{{ $log->new_status == 'Approved' ? 'success' : ($log->new_status == 'Received' ? 'success' : ($log->new_status == 'Pending' ? 'warning' : ($log->new_status == 'Rejected' ? 'danger' : 'info'))) }}">
                                        {{ $log->new_status }}
                                    </span>
                                    @php
                                        // Extract "to department" information from remarks if it's a forward
                                        $toInfo = '';
                                        if (Str::contains($log->remarks, 'to')) {
                                            preg_match('/to (.+)/', $log->remarks, $matches);
                                            if (isset($matches[1])) {
                                                $toInfo = trim($matches[1]);
                                                // Remove any trailing period
                                                $toInfo = rtrim($toInfo, '.');
                                            }
                                        }
                                    @endphp
                                    @if($toInfo)
                                    <span class="text-muted">to</span>
                                    <span class="badge bg-primary">{{ $toInfo }}</span>
                                    @endif
                                </div>
                                <div class="text-muted small">
                                    by <strong>{{ $log->updatedBy ? $log->updatedBy->name : 'System' }}</strong>
                                    @if($log->updatedBy && $log->updatedBy->department)
                                    <span class="text-muted">({{ $log->updatedBy->department->name }})</span>
                                    @endif
                                    @if($log->remarks)
                                    <br><em>{{ $log->remarks }}</em>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted">No status history available</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-qr-code"></i> QR Code</h5>
                </div>
                <div class="card-body text-center">
                    @if($document->qr_code_path)
                    <img src="{{ asset($document->qr_code_path) }}" alt="QR Code" class="img-fluid" style="max-width: 250px;">
                    @else
                    <p class="text-muted">QR Code not available</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Archive Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Archived</small>
                        <div>{{ $document->archived_at->diffForHumans() }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Total Processing Time</small>
                        <div>{{ $document->created_at->diffInDays($document->archived_at) }} days</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Status Changes</small>
                        <div>{{ $document->statusLogs->count() }} updates</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

