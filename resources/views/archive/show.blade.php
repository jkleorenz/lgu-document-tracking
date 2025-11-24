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
                    @php
                        // Get the status before archiving to show correct badge
                        $preArchiveStatus = $document->getPreArchiveStatus();
                        $displayStatus = $preArchiveStatus ?: $document->status;
                    @endphp
                    @if($displayStatus == 'Approved')
                    <i class="bi bi-check-circle-fill text-success ms-2" title="Approved"></i>
                    @endif
                    @if($displayStatus == 'Rejected')
                    <i class="bi bi-x-circle-fill text-danger ms-2" title="Rejected"></i>
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
                <i class="bi bi-archive"></i> This document was archived on {{ $document->archived_at ? $document->archived_at->format('F d, Y h:i A') : 'N/A' }}
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
                            <th>Current Status:</th>
                            <td>
                                @php
                                    // Get the status before archiving to show correct badge
                                    $preArchiveStatus = $document->getPreArchiveStatus();
                                    $displayStatus = $preArchiveStatus ?: $document->status;
                                @endphp
                                <span class="badge bg-{{ $displayStatus == 'Approved' ? 'success' : ($displayStatus == 'Rejected' ? 'danger' : 'secondary') }}">
                                    {{ $document->status }}
                                </span>
                                @if($preArchiveStatus && $preArchiveStatus !== 'Archived')
                                <span class="badge bg-{{ $preArchiveStatus == 'Approved' ? 'success' : ($preArchiveStatus == 'Completed' ? 'primary' : ($preArchiveStatus == 'Rejected' ? 'danger' : ($preArchiveStatus == 'Return' ? 'danger' : 'secondary'))) }} ms-2">
                                    {{ $preArchiveStatus }}
                                </span>
                                @endif
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
                            <td>{{ $document->archived_at ? $document->archived_at->format('F d, Y h:i A') : 'N/A' }}</td>
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
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-qr-code"></i> QR Code</h5>
                </div>
                <div class="card-body text-center">
                    @if($document->qr_code_path)
                    <img src="{{ asset($document->qr_code_path) }}" alt="QR Code" id="qr-code-image-archive" class="img-fluid mb-3" style="max-width: 250px;">
                    <div class="d-grid gap-2">
                        <a href="{{ route('documents.print-qr', $document) }}" class="btn btn-primary" target="_blank">
                            <i class="bi bi-printer"></i> Print QR Code
                        </a>
                        <button type="button" class="btn btn-success" onclick="saveQRCodeArchive()">
                            <i class="bi bi-download"></i> Save QR Code
                        </button>
                    </div>
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
                        <div>{{ $document->archived_at ? $document->archived_at->diffForHumans() : 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Total Processing Time</small>
                        <div>{{ $document->archived_at ? $document->created_at->diffInDays($document->archived_at) : 'N/A' }} {{ $document->archived_at ? 'days' : '' }}</div>
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

@push('scripts')
<script>
function saveQRCodeArchive() {
    const qrCodeImage = document.getElementById('qr-code-image-archive');
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

