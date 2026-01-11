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
                    @php
                        // Get the status before archiving to show correct badge
                        $preArchiveStatus = $document->getPreArchiveStatus();
                        $displayStatus = $preArchiveStatus ?: $document->status;
                    @endphp
                    @if($displayStatus == 'Approved')
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

                <!-- Document Review Action Buttons -->
                @if($document->status != 'Pending Verification' && $document->status != 'Rejected' && $document->status != 'Archived' && $document->status != 'Approved')
                @if(in_array($document->status, ['Received', 'Under Review']))
                <form method="POST" action="{{ route('documents.updateStatus', $document) }}" style="display: inline-block; margin: 0;">
                    @csrf
                    <input type="hidden" name="status" value="Completed">
                    <button type="submit" class="btn btn-primary btn-uniform" title="Complete Document" onclick="return confirm('Mark this document as Completed?')">
                        <i class="bi bi-check-lg"></i>
                    </button>
                </form>
                @endif
                @if(in_array($document->status, ['Received', 'Under Review']))
                <button type="button" class="btn btn-warning btn-uniform" title="Return Document" data-bs-toggle="modal" data-bs-target="#returnModal">
                    <i class="bi bi-arrow-return-left"></i>
                </button>
                @endif
                @endif

                @can('manage-documents')
                @if($document->created_by == auth()->id() || auth()->user()->hasAnyRole(['Administrator', 'Mayor']))
                <a href="{{ route('documents.edit', $document) }}" class="btn btn-warning btn-uniform" title="Edit Document">
                    <i class="bi bi-pencil"></i>
                </a>
                @endif
                @endcan
                <a href="{{ route('documents.print-qr', $document) }}" class="btn btn-secondary btn-uniform" target="_blank" title="Print QR Code">
                    <i class="bi bi-qr-code"></i>
                </a>
                <a href="{{ route('documents.report', ['document' => $document->id, 'format' => 'pdf']) }}" class="btn btn-info btn-uniform" title="Generate Report">
                    <i class="bi bi-file-earmark-text"></i>
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
                            <th>Current Status:</th>
                            <td>
                                @php
                                    // For archived documents, get the pre-archive status for display
                                    $displayStatus = $document->status;
                                    $preArchiveStatus = null;
                                    
                                    // Check if document was manually archived (status changed to 'Archived')
                                    if ($document->status === 'Archived') {
                                        $preArchiveStatus = $document->getPreArchiveStatus();
                                        if ($preArchiveStatus && ($preArchiveStatus === 'Rejected' || $preArchiveStatus === 'Approved' || $preArchiveStatus === 'Completed')) {
                                            $displayStatus = $preArchiveStatus;
                                        }
                                    }
                                    // If status is 'Approved' and archived_at is set, it's auto-archived - show 'Approved'
                                    // This case doesn't need special handling as status is already 'Approved'
                                @endphp
                                <span class="badge bg-{{ $displayStatus == 'Approved' ? 'success' : ($displayStatus == 'Completed' ? 'primary' : ($displayStatus == 'Return' ? 'danger' : ($displayStatus == 'Received' ? 'success' : ($displayStatus == 'Retrieved' ? 'info' : ($displayStatus == 'Pending' ? 'warning' : ($displayStatus == 'Pending Verification' ? 'warning' : ($displayStatus == 'Rejected' ? 'danger' : 'info'))))))) }}">
                                    {{ $displayStatus }}
                                </span>
                                @if($displayStatus == 'Pending Verification')
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="bi bi-shield-exclamation"></i> AWAITING ADMIN APPROVAL
                                </span>
                                @endif
                                @if($displayStatus == 'Rejected')
                                <span class="badge bg-danger text-white ms-2">
                                    <i class="bi bi-x-circle-fill"></i> DOCUMENT REJECTED
                                </span>
                                @endif
                                @if($displayStatus == 'Approved')
                                <span class="badge bg-success text-white ms-2">
                                    <i class="bi bi-check-circle-fill"></i> DOCUMENT APPROVED
                                </span>
                                @endif
                                @if($displayStatus == 'Completed')
                                <span class="badge bg-primary text-white ms-2">
                                    <i class="bi bi-check-circle-fill"></i> DOCUMENT COMPLETED
                                </span>
                                @endif
                                @if($displayStatus == 'Return')
                                <span class="badge bg-danger text-white ms-2">
                                    <i class="bi bi-arrow-return-left"></i> DOCUMENT RETURNED
                                </span>
                                @endif
                                @if($document->is_priority)
                                <span class="badge badge-priority ms-2">PRIORITY</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Current Department:</th>
                            <td>
                                @if(in_array($document->status, ['Forwarded', 'Pending']))
                                <span class="text-muted">N/A</span>
                                @elseif($document->department)
                                {{ $document->department->display_name }}
                                @else
                                <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Last Location:</th>
                            <td>{{ $lastLocation ?? 'N/A' }}</td>
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
                                    @php
                                        // Check if this is a return action
                                        $isReturn = $log->new_status == 'Return';
                                        
                                        // Determine badge color - red for returns, otherwise use standard colors
                                        $badgeColor = 'info';
                                        if ($isReturn) {
                                            $badgeColor = 'danger';
                                        } elseif ($log->new_status == 'Approved') {
                                            $badgeColor = 'success';
                                        } elseif ($log->new_status == 'Completed') {
                                            $badgeColor = 'primary';
                                        } elseif ($log->new_status == 'Received') {
                                            $badgeColor = 'success';
                                        } elseif ($log->new_status == 'Retrieved') {
                                            $badgeColor = 'info';
                                        } elseif ($log->new_status == 'Pending') {
                                            $badgeColor = 'warning';
                                        } elseif ($log->new_status == 'Rejected') {
                                            $badgeColor = 'danger';
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">
                                        {{ $log->new_status }}
                                    </span>
                                </div>
                                <div class="text-muted small">
                                    @if($log->updatedBy && $log->updatedBy->department)
                                    by <strong>{{ $log->updatedBy->department->name }}</strong>
                                    @else
                                    by <strong>{{ $log->updatedBy ? $log->updatedBy->name : 'System' }}</strong>
                                    @endif
                                    @if($log->remarks && $isReturn)
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
                    <img src="{{ asset($document->qr_code_path) }}" alt="QR Code" id="qr-code-image" class="img-fluid mb-3" style="max-width: 250px;">
                    <div class="d-grid gap-2">
                        <a href="{{ route('documents.print-qr', $document) }}" class="btn btn-primary" target="_blank">
                            <i class="bi bi-printer"></i> Print QR Code
                        </a>
                        <button type="button" class="btn btn-success" onclick="saveQRCode()">
                            <i class="bi bi-download"></i> Save QR Code
                        </button>
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

    <!-- Return Document Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="returnModalLabel">
                        <i class="bi bi-arrow-return-left"></i> Return Document
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('documents.updateStatus', $document) }}">
                    @csrf
                    <input type="hidden" name="status" value="Return">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> The document will be marked as returned and made available for review again.
                        </div>
                        <div class="mb-3">
                            <label for="return_remarks" class="form-label">Return Remarks <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="return_remarks" name="remarks" rows="4" required placeholder="Explain why the document is being returned..."></textarea>
                            <small class="form-text text-muted">Provide details about why this document is being returned for further review or action.</small>
                        </div>
                        <div class="mb-3">
                            <label for="return_department" class="form-label">Return To Department <span class="text-danger">*</span></label>
                            <select class="form-select" id="return_department" name="return_to_department" required>
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->display_name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select which department to return this document to.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-return-left"></i> Return Document
                        </button>
                    </div>
                </form>
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

@push('scripts')
<script>
function saveQRCode() {
    const qrCodeImage = document.getElementById('qr-code-image');
    if (!qrCodeImage) {
        alert('QR Code image not found.');
        return;
    }
    
    // Get the image source
    const imageUrl = qrCodeImage.src;
    const documentNumber = '{{ $document->document_number }}';
    
    // Create a new image to load the QR code
    const img = new Image();
    img.crossOrigin = 'anonymous'; // Handle CORS if needed
    
    img.onload = function() {
        // Create a canvas element
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Set canvas size to match image (with higher resolution for better quality)
        const scale = 2; // 2x resolution for better quality
        canvas.width = img.width * scale;
        canvas.height = img.height * scale;
        
        // Scale the context for higher resolution
        ctx.scale(scale, scale);
        
        // Draw the image on canvas
        ctx.drawImage(img, 0, 0);
        
        // Convert canvas to PNG blob
        canvas.toBlob(function(blob) {
            if (!blob) {
                alert('Failed to convert QR code to PNG.');
                return;
            }
            
            // Create download link
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'QRCode_' + documentNumber + '.png';
            
            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Clean up
            URL.revokeObjectURL(url);
        }, 'image/png', 1.0); // PNG format with maximum quality
    };
    
    img.onerror = function() {
        alert('Failed to load QR code image. Please try again.');
    };
    
    // Load the image
    img.src = imageUrl;
}
</script>
@endpush

@endsection

