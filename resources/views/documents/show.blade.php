@extends('layouts.app')

@section('title', 'Document Details')

@section('content')
<style>
    /* Buttons with text */
    .btn-uniform {
        min-width: 160px;
        padding: 8px 16px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 0.875rem;
        white-space: nowrap;
        height: 38px;
        line-height: 1.2;
        border: none;
        transition: all 0.2s ease;
    }
    
    .btn-uniform:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    /* Icon-only buttons - uniform square size */
    .btn-uniform i:only-child {
        margin: 0;
    }
    
    .btn-uniform:not(:has(i + *)) {
        min-width: 38px;
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-uniform i {
        font-size: 0.95rem;
        flex-shrink: 0;
    }
</style>

<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
                <li class="breadcrumb-item active">{{ $document->document_number }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 class="fw-bold">
                    <i class="bi bi-file-earmark-text"></i> {{ $document->title }}
                    @if($document->status == 'Approved')
                    <i class="bi bi-check-circle-fill text-success ms-2" title="Approved"></i>
                    @endif
                </h2>
                <p class="text-muted">{{ $document->document_number }}</p>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <!-- Administrator Verification Buttons -->
                @role('Administrator')
                @if($document->status == 'Pending Verification')
                <form method="POST" action="{{ route('documents.approve', $document) }}" style="display: inline-block; margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-success btn-uniform" onclick="return confirm('Approve this document and forward to department?')">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                </form>
                <button type="button" class="btn btn-danger btn-uniform" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle"></i> Reject
                </button>
                @endif
                @endrole
                
                @can('manage-documents')
                @if($document->created_by == auth()->id() || auth()->user()->hasRole('Administrator'))
                <a href="{{ route('documents.edit', $document) }}" class="btn btn-warning btn-uniform" title="Edit Document">
                    <i class="bi bi-pencil"></i>
                </a>
                @endif
                @endcan
                <a href="{{ route('documents.print-qr', $document) }}" class="btn btn-secondary btn-uniform" target="_blank" title="Print QR Code">
                    <i class="bi bi-qr-code"></i>
                </a>
                @can('archive-documents')
                @if($document->status != 'Archived')
                <form method="POST" action="{{ route('documents.archive', $document) }}" style="display: inline-block; margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-dark btn-uniform" title="Archive Document" onclick="return confirm('Are you sure you want to archive this document?')">
                        <i class="bi bi-archive"></i>
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Document Details -->
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
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Received' ? 'success' : ($document->status == 'Pending' ? 'warning' : ($document->status == 'Pending Verification' ? 'warning' : ($document->status == 'Rejected' ? 'danger' : 'info')))) }}">
                                    {{ $document->status }}
                                </span>
                                @if($document->status == 'Pending Verification')
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="bi bi-shield-exclamation"></i> AWAITING ADMIN APPROVAL
                                </span>
                                @endif
                                @if($document->is_priority)
                                <span class="badge badge-priority ms-2">PRIORITY</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td>
                                @if($document->department)
                                {{ $document->department->name }} ({{ $document->department->code }})
                                @else
                                <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td>{{ $document->creator ? $document->creator->name : 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <th>Current Handler:</th>
                            <td>
                                @if($document->currentHandler)
                                {{ $document->currentHandler->name }}
                                @else
                                <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created Date:</th>
                            <td>{{ $document->created_at->format('F d, Y h:i A') }}</td>
                        </tr>
                        @if($document->archived_at)
                        <tr>
                            <th>Archived Date:</th>
                            <td>{{ $document->archived_at->format('F d, Y h:i A') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Description:</th>
                            <td>{{ $document->description ?? 'No description provided' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Update Status Form -->
            @if($document->status != 'Archived')
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Update Document Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('documents.update-status', $document) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">New Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Pending" {{ $document->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Received" {{ $document->status == 'Received' ? 'selected' : '' }}>Received</option>
                                    <option value="Under Review" {{ $document->status == 'Under Review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="Forwarded" {{ $document->status == 'Forwarded' ? 'selected' : '' }}>Forwarded</option>
                                    <option value="Approved" {{ $document->status == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Rejected" {{ $document->status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="remarks" class="form-label">Remarks (Optional)</label>
                                <input type="text" class="form-control" id="remarks" name="remarks" placeholder="Add remarks...">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="forward_to_department_status" class="form-label">
                                    <i class="bi bi-arrow-right-circle"></i> Forward to Department (Optional)
                                </label>
                                <select class="form-select" id="forward_to_department_status" name="forward_to_department">
                                    <option value="">-- No Forwarding --</option>
                                    @php
                                        $departments = \App\Models\Department::where('is_active', true)->get();
                                    @endphp
                                    @foreach($departments as $dept)
                                    @if($dept->id != $document->department_id)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                    @endif
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    <i class="bi bi-info-circle"></i> Current: <strong>{{ $document->department ? $document->department->name : 'Unassigned' }}</strong>
                                </small>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Status History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Document History</h5>
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
            <!-- QR Code -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-qr-code"></i> QR Code</h5>
                </div>
                <div class="card-body text-center">
                    @if($document->qr_code_path)
                    <img src="{{ asset($document->qr_code_path) }}" alt="QR Code" class="img-fluid mb-3" style="max-width: 250px;">
                    <div class="d-grid gap-2">
                        <a href="{{ route('documents.print-qr', $document) }}" class="btn btn-primary" target="_blank">
                            <i class="bi bi-printer"></i> Print QR Code
                        </a>
                    </div>
                    @else
                    <p class="text-muted">QR Code not available</p>
                    @endif
                </div>
            </div>

            <!-- Quick Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Quick Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Created</small>
                        <div>{{ $document->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Last Updated</small>
                        <div>{{ $document->updated_at->diffForHumans() }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Total Status Changes</small>
                        <div>{{ $document->statusLogs->count() }} updates</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Document Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="bi bi-x-circle"></i> Reject Document
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('documents.reject', $document) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> You are about to reject this document. Please provide a reason.
                        </div>
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Enter reason for rejection..."></textarea>
                            <small class="form-text text-muted">The document creator will be notified with this reason.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Reject Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

