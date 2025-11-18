@extends('layouts.app')

@section('title', 'QR Code Scanner')

@section('content')
<style>
    /* Scanner Input Field Styling */
    #scanner_input {
        font-size: 1.2rem;
        font-weight: 600;
        border: 2px solid #0d6efd;
        transition: all 0.3s ease;
    }
    
    #scanner_input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        transform: scale(1.02);
    }
    
    /* Scanner Ready Animation */
    @keyframes pulse-border {
        0%, 100% {
            border-color: #0d6efd;
        }
        50% {
            border-color: #0dcaf0;
        }
    }
    
    #scanner_input:focus {
        animation: pulse-border 2s infinite;
    }
    
    /* Scanner Icon Animation */
    @keyframes scan-pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }
    
    .bi-upc-scan {
        animation: scan-pulse 2s infinite;
    }
    
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
                    <!-- Hardware Scanner Section -->
                    <div id="scanner-section">
                        <div class="text-center mb-4">
                            <div style="font-size: 4rem; color: #0d6efd;">
                                <i class="bi bi-upc-scan"></i>
                            </div>
                            <h3 class="mt-3">Hardware QR Scanner Ready</h3>
                            <p class="text-muted">Use your 2D scanner device to scan document QR codes</p>
                        </div>

                        <!-- Scanner Status -->
                        <div id="scanner-status" class="alert alert-success text-center" style="display: block;">
                            <i class="bi bi-check-circle"></i> <span id="status-message">Scanner Ready - Point your device at a QR code</span>
                        </div>

                        <!-- Scanner Input (Hidden field to capture scanner input) -->
                        <div class="text-center mb-4">
                            <div class="input-group" style="max-width: 600px; margin: 0 auto;">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="bi bi-upc-scan"></i>
                                </span>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="scanner_input" 
                                       placeholder="Waiting for scanner input..."
                                       autofocus
                                       autocomplete="off">
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="bi bi-info-circle"></i> This field will automatically capture scanner input
                            </small>
                        </div>

                        <!-- Scanner Activity Indicator -->
                        <div id="scanning-indicator" class="text-center" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Scanning...</span>
                            </div>
                            <p class="mt-2 text-muted">Processing scan...</p>
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
                        <li><strong>Hardware Scanner:</strong> Point your 2D scanner device at the QR code - it will automatically scan and process</li>
                        <li><strong>Manual Entry:</strong> Enter the document number in the field above and click Search</li>
                        <li>View document details and status after scanning/searching</li>
                        <li>Update document status if you have permission</li>
                    </ol>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-lightbulb"></i> <strong>Tip:</strong> Make sure the scanner input field is focused (click on it if needed). The QR code is printed on the physical document for quick access.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Hardware Scanner Implementation
let scannerBuffer = '';
let scannerTimeout = null;
let isProcessing = false;
let isManualEntryActive = false; // Flag to track if manual entry field is being used

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
    return qrData.trim();
}

// Initialize Hardware Scanner Listener
function initializeHardwareScanner() {
    const scannerInput = document.getElementById('scanner_input');
    const statusDiv = document.getElementById('scanner-status');
    const statusMsg = document.getElementById('status-message');
    const scanningIndicator = document.getElementById('scanning-indicator');
    
    if (!scannerInput) return;
    
    // Listen for input events (scanner sends characters rapidly)
    scannerInput.addEventListener('input', function(e) {
        if (isProcessing) return;
        
        // Clear existing timeout
        if (scannerTimeout) {
            clearTimeout(scannerTimeout);
        }
        
        // Set a timeout to process the complete scan
        // Hardware scanners typically send all data within 50-100ms
        scannerTimeout = setTimeout(() => {
            processScannerInput(scannerInput.value);
        }, 150);
    });
    
    // Also listen for Enter key (most scanners send Enter after scan)
    scannerInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && scannerInput.value.trim() !== '') {
            e.preventDefault();
            if (scannerTimeout) {
                clearTimeout(scannerTimeout);
            }
            processScannerInput(scannerInput.value);
        }
    });
    
    // Keep focus on scanner input, but only if manual entry is not active
    scannerInput.addEventListener('blur', function(e) {
        setTimeout(() => {
            const scannerInterface = document.getElementById('scanner-interface');
            const manualInput = document.getElementById('document_number');
            const activeElement = document.activeElement;
            
            // Only refocus scanner if:
            // 1. Scanner interface is visible
            // 2. Manual entry is not active
            // 3. User is not focusing on manual entry field or form elements
            // 4. User clicked somewhere else (not on an input field)
            if (scannerInterface && scannerInterface.style.display !== 'none' && !isManualEntryActive) {
                const isManualEntryField = activeElement === manualInput;
                const isFormElement = activeElement && (
                    activeElement.closest('#scanForm') || 
                    activeElement.type === 'submit' || 
                    activeElement.tagName === 'BUTTON'
                );
                const isInputField = activeElement && (
                    activeElement.tagName === 'INPUT' || 
                    activeElement.tagName === 'TEXTAREA' || 
                    activeElement.tagName === 'SELECT'
                );
                
                // Don't refocus if user is interacting with manual entry form
                if (!isManualEntryField && !isFormElement && !isInputField) {
                    scannerInput.focus();
                }
            }
        }, 150);
    });
    
    // Initial focus (but only if manual entry field isn't being used)
    setTimeout(() => {
        if (!isManualEntryActive) {
            scannerInput.focus();
        }
    }, 300);
}

// Process scanned input
function processScannerInput(scannedData) {
    if (!scannedData || scannedData.trim() === '' || isProcessing) {
        return;
    }
    
    isProcessing = true;
    const scannerInput = document.getElementById('scanner_input');
    const statusDiv = document.getElementById('scanner-status');
    const statusMsg = document.getElementById('status-message');
    const scanningIndicator = document.getElementById('scanning-indicator');
    
    // Extract document number
    const documentNumber = extractDocumentNumber(scannedData);
    console.log('Hardware Scanner detected:', scannedData);
    console.log('Extracted document number:', documentNumber);
    
    // Update UI
    statusMsg.textContent = `Scanned: ${documentNumber}`;
    statusDiv.className = 'alert alert-success text-center';
    scanningIndicator.style.display = 'block';
    
    // Clear input
    scannerInput.value = '';
    
    // Submit the scanned document number
    submitDocumentNumber(documentNumber);
}

// Submit document number (from scan or manual entry)
function submitDocumentNumber(documentNumber) {
    const statusDiv = document.getElementById('scanner-status');
    const statusMsg = document.getElementById('status-message');
    const scanningIndicator = document.getElementById('scanning-indicator');
    
    statusDiv.style.display = 'block';
    statusMsg.textContent = 'Looking up document...';
    statusDiv.className = 'alert alert-info text-center';
    
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
    .then(response => {
        // Check if response is OK
        if (!response.ok) {
            // Handle HTTP error status codes
            return response.json().catch(() => {
                throw new Error(`Server error (${response.status}). Please try again.`);
            }).then(errorData => {
                throw new Error(errorData.message || `Error: ${response.statusText || response.status}`);
            });
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, try to get text and show error
            return response.text().then(text => {
                throw new Error('Invalid response from server. Please try again.');
            });
        }
    })
    .then(data => {
        scanningIndicator.style.display = 'none';
        
        if (data.success && data.document) {
            statusMsg.textContent = 'Document found! Loading details...';
            statusDiv.className = 'alert alert-success text-center';
            
            // Clear input fields
            document.getElementById('scanner_input').value = '';
            document.getElementById('document_number').value = '';
            
            // Clear manual entry flag
            isManualEntryActive = false;
            
            // Display document information
            displayDocument(data.document, data.redirect_url);
            
            // Hide scanner interface
            document.getElementById('scanner-interface').style.display = 'none';
            statusDiv.style.display = 'none';
            
            // Reset processing flag
            isProcessing = false;
        } else {
            statusMsg.textContent = data.message || 'Document not found';
            statusDiv.className = 'alert alert-danger text-center';
            
            // Reset processing flag and refocus after delay
            setTimeout(() => {
                statusDiv.className = 'alert alert-success text-center';
                statusMsg.textContent = 'Scanner Ready - Point your device at a QR code';
                isProcessing = false;
                
                // Refocus on the field that was used (manual entry or scanner)
                const manualInput = document.getElementById('document_number');
                const scannerInput = document.getElementById('scanner_input');
                
                // If manual input has value or was recently focused, refocus it
                if (manualInput.value || (document.activeElement && document.activeElement.id === 'document_number')) {
                    isManualEntryActive = true;
                    manualInput.focus();
                    manualInput.select(); // Select the text for easy re-entry
                } else {
                    isManualEntryActive = false;
                    scannerInput?.focus();
                }
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        scanningIndicator.style.display = 'none';
        statusMsg.textContent = 'Error processing request. Please try again.';
        statusDiv.className = 'alert alert-danger text-center';
        
        // Reset processing flag and refocus after delay
        setTimeout(() => {
            statusDiv.className = 'alert alert-success text-center';
            statusMsg.textContent = 'Scanner Ready - Point your device at a QR code';
            isProcessing = false;
            
            // Refocus on the field that was used (manual entry or scanner)
            const manualInput = document.getElementById('document_number');
            const scannerInput = document.getElementById('scanner_input');
            
            // If manual input has value or was recently focused, refocus it
            if (manualInput.value || (document.activeElement && document.activeElement.id === 'document_number')) {
                isManualEntryActive = true;
                manualInput.focus();
                manualInput.select(); // Select the text for easy re-entry
            } else {
                isManualEntryActive = false;
                scannerInput?.focus();
            }
        }, 3000);
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
    
    // Reset forms
    document.getElementById('document_number').value = '';
    document.getElementById('scanner_input').value = '';
    
    // Reset manual entry flag
    isManualEntryActive = false;
    
    // Reset status
    const statusDiv = document.getElementById('scanner-status');
    const statusMsg = document.getElementById('status-message');
    const scanningIndicator = document.getElementById('scanning-indicator');
    
    statusDiv.style.display = 'block';
    statusDiv.className = 'alert alert-success text-center';
    statusMsg.textContent = 'Scanner Ready - Point your device at a QR code';
    scanningIndicator.style.display = 'none';
    
    // Reset processing flag
    isProcessing = false;
    
    // Refocus scanner input (after a short delay to ensure everything is reset)
    setTimeout(() => {
        if (!isManualEntryActive) {
            document.getElementById('scanner_input')?.focus();
        }
    }, 100);
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Initialize hardware scanner and manual form on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize hardware scanner
    initializeHardwareScanner();
    console.log('Hardware Scanner initialized and ready');
    
    // Manual entry field focus management
    const manualInput = document.getElementById('document_number');
    if (manualInput) {
        // Set flag when manual entry field gets focus
        manualInput.addEventListener('focus', function() {
            isManualEntryActive = true;
            console.log('Manual entry active - scanner auto-focus disabled');
        });
        
        // Clear flag when manual entry field loses focus (but only if not submitting)
        manualInput.addEventListener('blur', function() {
            // Delay clearing flag to allow form submission to proceed
            setTimeout(() => {
                // Only clear if the focus didn't move to the submit button or form elements
                const activeElement = document.activeElement;
                const isFormElement = activeElement && (
                    activeElement.closest('#scanForm') || 
                    activeElement.type === 'submit' || 
                    activeElement.tagName === 'BUTTON'
                );
                
                if (!isFormElement) {
                    isManualEntryActive = false;
                    console.log('Manual entry inactive - scanner auto-focus enabled');
                }
            }, 200);
        });
        
        // Also handle clicks on the input field
        manualInput.addEventListener('click', function() {
            isManualEntryActive = true;
            manualInput.focus();
        });
    }
    
    // Manual form submission handler
    const scanForm = document.getElementById('scanForm');
    if (scanForm) {
        scanForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const documentNumberInput = document.getElementById('document_number');
            const documentNumber = documentNumberInput.value.trim();
            
            // Validate input
            if (!documentNumber) {
                const statusDiv = document.getElementById('scanner-status');
                const statusMsg = document.getElementById('status-message');
                
                statusDiv.style.display = 'block';
                statusMsg.textContent = 'Please enter a document number';
                statusDiv.className = 'alert alert-warning text-center';
                
                documentNumberInput.focus();
                return;
            }
            
            // Show processing indicator for manual entry
            const statusDiv = document.getElementById('scanner-status');
            const statusMsg = document.getElementById('status-message');
            const scanningIndicator = document.getElementById('scanning-indicator');
            
            statusDiv.style.display = 'block';
            statusMsg.textContent = 'Searching for document...';
            statusDiv.className = 'alert alert-info text-center';
            scanningIndicator.style.display = 'block';
            
            // Submit the document number
            submitDocumentNumber(documentNumber);
        });
        console.log('Manual entry form handler initialized');
    }
});
</script>
@endsection

