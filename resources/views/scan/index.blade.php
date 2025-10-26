@extends('layouts.app')

@section('title', 'QR Code Scanner')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi bi-qr-code-scan"></i> QR Code Scanner</h2>
        <p class="text-muted">Scan document QR codes to view and update status</p>
    </div>

    @if(isset($document))
    <!-- Document Found -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Document Found!</h5>
                </div>
                <div class="card-body">
                    <h4>{{ $document->title }}</h4>
                    
                    <table class="table table-borderless mt-3">
                        <tr>
                            <th width="200">Document Number:</th>
                            <td><strong>{{ $document->document_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td><span class="badge bg-secondary">{{ $document->document_type }}</span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Pending' ? 'warning' : 'info') }}">
                                    {{ $document->status }}
                                </span>
                                @if($document->is_priority)
                                <span class="badge badge-priority ms-2">PRIORITY</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td>{{ $document->department->name }}</td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td>{{ $document->creator->name }}</td>
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
                            <th>Description:</th>
                            <td>{{ $document->description ?? 'No description' }}</td>
                        </tr>
                    </table>

                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('documents.show', $document) }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> View Full Details
                        </a>
                        <a href="{{ route('scan.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Scan Another
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
                </div>
                <div class="card-body">
                    @forelse($document->statusLogs->take(5) as $log)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="mb-1">
                            <span class="badge bg-{{ $log->new_status == 'Approved' ? 'success' : ($log->new_status == 'Received' ? 'success' : ($log->new_status == 'Pending' ? 'warning' : ($log->new_status == 'Rejected' ? 'danger' : 'info'))) }}">
                                {{ $log->new_status }}
                            </span>
                        </div>
                        <small class="text-muted">
                            {{ $log->action_date->diffForHumans() }}<br>
                            by {{ $log->updatedBy->name }}
                        </small>
                    </div>
                    @empty
                    <p class="text-muted">No activity yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Scanner Interface -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center p-5">
                    <div style="font-size: 5rem; color: #0d6efd;">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h3 class="mt-3">Scan QR Code</h3>
                    <p class="text-muted">Enter document number manually or use camera to scan</p>

                    <form method="POST" action="{{ route('scan.process') }}" id="scanForm" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <input type="text" 
                                   class="form-control form-control-lg text-center" 
                                   name="document_number" 
                                   id="document_number" 
                                   placeholder="Enter Document Number"
                                   required
                                   autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-search"></i> Search Document
                        </button>
                    </form>

                    <div class="mt-4">
                        <button class="btn btn-outline-secondary" onclick="alert('Camera scanning feature requires additional JavaScript library (ZXing). For demo, please enter document number manually.')">
                            <i class="bi bi-camera"></i> Use Camera Scanner
                        </button>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> How to Use</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Enter the document number in the field above, OR</li>
                        <li>Click "Use Camera Scanner" to scan QR code with your device camera</li>
                        <li>View document details and status</li>
                        <li>Update document status if you have permission</li>
                    </ol>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-lightbulb"></i> <strong>Tip:</strong> The QR code is printed on the physical document. Scan it to quickly access document information.
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.getElementById('scanForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("scan.process") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.redirect_url) {
            window.location.href = data.redirect_url;
        } else {
            alert(data.message || 'Document not found');
        }
    })
    .catch(error => {
        alert('Error scanning document. Please try again.');
        console.error('Error:', error);
    });
});
</script>
@endsection

