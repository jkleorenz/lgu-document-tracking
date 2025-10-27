@extends('layouts.app')

@section('title', 'QR Code Scanner')

@section('content')
<style>
    /* Document Scanned Successfully Header */
    .card-header.bg-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        color: #ffffff !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    
    /* Document Whereabouts Header */
    .card-header.bg-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%) !important;
        color: #ffffff !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }
    
    .card-header h5 {
        color: #ffffff !important;
        font-weight: 600;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        letter-spacing: 0.3px;
    }
    
    .card-header i {
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.2));
    }
    
    /* Current Location Alert Box */
    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%) !important;
        border: 2px solid #17a2b8 !important;
        border-left: 5px solid #17a2b8 !important;
        color: #0c5460 !important;
        box-shadow: 0 2px 8px rgba(23, 162, 184, 0.2);
    }
    
    .alert-info strong {
        color: #044654 !important;
        font-weight: 700;
    }
    
    .alert-info .bi-geo-alt-fill {
        color: #17a2b8 !important;
    }
    
    /* Quick Info Card Header - Same style as Document Whereabouts */
    .card-header:has(.bi-info-circle) {
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%) !important;
        color: #ffffff !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }
    
    .card-header:has(.bi-info-circle) h5 {
        color: #ffffff !important;
        font-weight: 600;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        letter-spacing: 0.3px;
    }
    
    .card-header:has(.bi-info-circle) i {
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.2));
    }
</style>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi bi-qr-code-scan"></i> QR Code Scanner</h2>
        <p class="text-muted">Scan document QR codes to view and update status</p>
    </div>

    <!-- Document Result Display (Hidden by default, shown after scan) -->
    <div id="document-result" class="row" style="display: none;">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Document Scanned Successfully!</h5>
                </div>
                <div class="card-body">
                    <h4 id="doc-title"></h4>
                    
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt-fill" style="font-size: 2rem; margin-right: 15px;"></i>
                            <div>
                                <strong>Current Location:</strong>
                                <div id="doc-location" class="fs-5"></div>
                            </div>
                        </div>
                    </div>
                    
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Document Number:</th>
                            <td><strong id="doc-number"></strong></td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td><span class="badge bg-secondary" id="doc-type"></span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span id="doc-status-badge"></span>
                                <span id="doc-priority-badge" style="display: none;" class="badge badge-priority ms-2">PRIORITY</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td id="doc-department"></td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td id="doc-creator"></td>
                        </tr>
                        <tr>
                            <th>Created Date:</th>
                            <td id="doc-created"></td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td id="doc-description"></td>
                        </tr>
                    </table>

                    <div class="d-flex gap-2 mt-4">
                        <a id="view-full-details" href="#" class="btn btn-primary">
                            <i class="bi bi-eye"></i> View Full Details
                        </a>
                        <button onclick="resetScanner()" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Scan Another Document
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pin-map-fill"></i> Document Whereabouts</h5>
                </div>
                <div class="card-body">
                    <div class="text-center p-3">
                        <i class="bi bi-building" style="font-size: 3rem; color: #0d6efd;"></i>
                        <h5 class="mt-3" id="whereabouts-dept"></h5>
                        <div class="mt-3">
                            <span id="whereabouts-status-badge"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Quick Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Document Number</small>
                        <div class="fw-bold" id="quick-doc-number"></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Status</small>
                        <div id="quick-status"></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Priority</small>
                        <div id="quick-priority"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Interface (Shown by default) -->
    <div id="scanner-interface" class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-4">
                    <!-- Camera Scanner Section -->
                    <div id="scanner-section">
                        <div class="text-center mb-4">
                            <div style="font-size: 4rem; color: #0d6efd;">
                                <i class="bi bi-qr-code-scan"></i>
                            </div>
                            <h3 class="mt-3">QR Code Scanner</h3>
                            <p class="text-muted">Use your camera to scan document QR codes</p>
                        </div>

                        <!-- Camera Preview -->
                        <div id="reader" style="display: none; max-width: 600px; margin: 0 auto;"></div>
                        
                        <!-- Scanner Controls -->
                        <div class="text-center mt-4">
                            <button id="start-scanner" class="btn btn-primary btn-lg">
                                <i class="bi bi-camera-video"></i> Start Camera Scanner
                            </button>
                            <button id="stop-scanner" class="btn btn-danger btn-lg" style="display: none;">
                                <i class="bi bi-stop-circle"></i> Stop Scanner
                            </button>
                        </div>

                        <!-- Scanner Status -->
                        <div id="scanner-status" class="alert alert-info mt-4" style="display: none;">
                            <i class="bi bi-info-circle"></i> <span id="status-message">Ready to scan...</span>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="text-center my-4">
                        <hr>
                        <span class="bg-white px-3 text-muted" style="position: relative; top: -20px;">OR</span>
                    </div>

                    <!-- Manual Entry Section -->
                    <div class="text-center">
                        <h5 class="mb-3">Enter Document Number Manually</h5>
                        <form method="POST" action="{{ route('scan.process') }}" id="scanForm">
                            @csrf
                            <div class="mb-3">
                                <input type="text" 
                                       class="form-control form-control-lg text-center" 
                                       name="document_number" 
                                       id="document_number" 
                                       placeholder="Enter Document Number"
                                       required>
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-search"></i> Search Document
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> How to Use</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li><strong>Camera Scanner:</strong> Click "Start Camera Scanner" and point your camera at the QR code</li>
                        <li><strong>Manual Entry:</strong> Enter the document number in the field above</li>
                        <li>View document details and status after scanning/searching</li>
                        <li>Update document status if you have permission</li>
                    </ol>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-lightbulb"></i> <strong>Tip:</strong> Make sure to allow camera permissions when prompted. The QR code is printed on the physical document for quick access.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Html5-QRCode Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
let html5QrCode = null;
let isScanning = false;

// Extract document number from QR code data
function extractDocumentNumber(qrData) {
    // If QR code contains a URL with document parameter
    if (qrData.includes('?document=') || qrData.includes('&document=')) {
        try {
            const url = new URL(qrData);
            const documentNumber = url.searchParams.get('document');
            if (documentNumber) {
                return documentNumber;
            }
        } catch (e) {
            console.log('Not a URL, using as-is');
        }
    }
    
    // If it's just the document number, return as-is
    return qrData;
}

// Start Scanner
document.getElementById('start-scanner')?.addEventListener('click', function() {
    const readerElement = document.getElementById('reader');
    const startBtn = document.getElementById('start-scanner');
    const stopBtn = document.getElementById('stop-scanner');
    const statusDiv = document.getElementById('scanner-status');
    const statusMsg = document.getElementById('status-message');
    
    // Show reader and update buttons
    readerElement.style.display = 'block';
    startBtn.style.display = 'none';
    stopBtn.style.display = 'inline-block';
    statusDiv.style.display = 'block';
    statusMsg.textContent = 'Initializing camera...';
    
    // Initialize scanner
    html5QrCode = new Html5Qrcode("reader");
    
    // Start scanning
    html5QrCode.start(
        { facingMode: "environment" }, // Use back camera
        {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        },
        (decodedText, decodedResult) => {
            // Success callback - QR code detected
            console.log('QR Code scanned:', decodedText);
            
            // Extract document number from URL or use as-is
            const documentNumber = extractDocumentNumber(decodedText);
            console.log('Extracted document number:', documentNumber);
            
            statusMsg.textContent = `QR Code detected! Document: ${documentNumber}`;
            statusDiv.className = 'alert alert-success mt-4';
            
            // Stop the scanner
            stopScanner();
            
            // Submit the scanned document number
            submitDocumentNumber(documentNumber);
        },
        (errorMessage) => {
            // Error callback - typically just means no QR code in view
            // We don't need to show this as it's constant
        }
    )
    .then(() => {
        isScanning = true;
        statusMsg.textContent = 'Camera active. Point at QR code to scan...';
        statusDiv.className = 'alert alert-info mt-4';
    })
    .catch((err) => {
        console.error('Unable to start scanner:', err);
        statusMsg.textContent = 'Error: Unable to access camera. Please check permissions.';
        statusDiv.className = 'alert alert-danger mt-4';
        readerElement.style.display = 'none';
        startBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
    });
});

// Stop Scanner
document.getElementById('stop-scanner')?.addEventListener('click', stopScanner);

function stopScanner() {
    if (html5QrCode && isScanning) {
        html5QrCode.stop()
            .then(() => {
                html5QrCode.clear();
                isScanning = false;
                
                // Reset UI
                document.getElementById('reader').style.display = 'none';
                document.getElementById('start-scanner').style.display = 'inline-block';
                document.getElementById('stop-scanner').style.display = 'none';
                document.getElementById('scanner-status').style.display = 'none';
            })
            .catch((err) => {
                console.error('Error stopping scanner:', err);
            });
    }
}

// Submit document number (from scan or manual entry)
function submitDocumentNumber(documentNumber) {
    const statusDiv = document.getElementById('scanner-status');
    const statusMsg = document.getElementById('status-message');
    
    statusDiv.style.display = 'block';
    statusMsg.textContent = 'Looking up document...';
    statusDiv.className = 'alert alert-info mt-4';
    
    const formData = new FormData();
    formData.append('document_number', documentNumber);
    formData.append('_token', '{{ csrf_token() }}');
    
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
        if (data.success && data.document) {
            statusMsg.textContent = 'Document found! Loading details...';
            statusDiv.className = 'alert alert-success mt-4';
            
            // Display document information
            displayDocument(data.document, data.redirect_url);
            
            // Hide scanner interface
            document.getElementById('scanner-interface').style.display = 'none';
            statusDiv.style.display = 'none';
        } else {
            statusMsg.textContent = data.message || 'Document not found';
            statusDiv.className = 'alert alert-danger mt-4';
            setTimeout(() => {
                document.getElementById('start-scanner').style.display = 'inline-block';
                statusDiv.style.display = 'none';
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        statusMsg.textContent = 'Error processing request. Please try again.';
        statusDiv.className = 'alert alert-danger mt-4';
    });
}

// Display document information
function displayDocument(doc, detailsUrl) {
    // Populate document details
    document.getElementById('doc-title').textContent = doc.title;
    document.getElementById('doc-number').textContent = doc.document_number;
    document.getElementById('doc-type').textContent = doc.document_type || 'N/A';
    document.getElementById('doc-department').textContent = doc.department;
    document.getElementById('doc-creator').textContent = doc.created_by;
    document.getElementById('doc-created').textContent = doc.created_at;
    document.getElementById('doc-description').textContent = doc.description || 'No description provided';
    document.getElementById('view-full-details').href = detailsUrl;
    
    // Current Location (big alert box)
    document.getElementById('doc-location').innerHTML = `
        <strong>${doc.department}</strong>
    `;
    
    // Status badge with color coding
    const statusColors = {
        'Approved': 'success',
        'Received': 'success',
        'Pending': 'warning',
        'Pending Verification': 'warning',
        'Under Review': 'info',
        'Forwarded': 'info',
        'Rejected': 'danger',
        'Archived': 'secondary'
    };
    const statusColor = statusColors[doc.status] || 'secondary';
    const statusBadge = `<span class="badge bg-${statusColor}">${doc.status}</span>`;
    
    document.getElementById('doc-status-badge').innerHTML = statusBadge;
    
    // Priority badge
    if (doc.is_priority) {
        document.getElementById('doc-priority-badge').style.display = 'inline-block';
    } else {
        document.getElementById('doc-priority-badge').style.display = 'none';
    }
    
    // Whereabouts sidebar
    document.getElementById('whereabouts-dept').textContent = doc.department;
    document.getElementById('whereabouts-status-badge').innerHTML = statusBadge;
    
    // Quick info sidebar
    document.getElementById('quick-doc-number').textContent = doc.document_number;
    document.getElementById('quick-status').innerHTML = statusBadge;
    document.getElementById('quick-priority').innerHTML = doc.is_priority 
        ? '<span class="badge badge-priority">PRIORITY</span>' 
        : '<span class="text-muted">Normal</span>';
    
    // Show document result section
    document.getElementById('document-result').style.display = 'flex';
    
    // Scroll to results
    document.getElementById('document-result').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Reset scanner to initial state
function resetScanner() {
    // Hide document result
    document.getElementById('document-result').style.display = 'none';
    
    // Show scanner interface
    document.getElementById('scanner-interface').style.display = 'flex';
    
    // Reset form
    document.getElementById('document_number').value = '';
    
    // Stop camera if running
    stopScanner();
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Manual form submission
document.getElementById('scanForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const documentNumber = document.getElementById('document_number').value;
    submitDocumentNumber(documentNumber);
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (html5QrCode && isScanning) {
        html5QrCode.stop();
    }
});
</script>
@endsection

