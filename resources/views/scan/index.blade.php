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
        <p class="text-muted">Scan document QR codes to receive document</p>
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
                            <th>Current Status:</th>
                            <td>
                                <span id="doc-status-badge"></span>
                                <span id="doc-priority-badge" style="display: none;" class="badge badge-priority ms-2">PRIORITY</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Current Department:</th>
                            <td id="doc-department"></td>
                        </tr>
                        <tr>
                            <th>Last Location:</th>
                            <td id="doc-last-location">N/A</td>
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
                        <div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="document-actions-btn">
                                <i class="bi bi-gear"></i> Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" id="complete-document-btn">
                                    <i class="bi bi-check-circle"></i> Complete
                                </a></li>
                                <li><a class="dropdown-item" href="#" id="return-document-btn">
                                    <i class="bi bi-arrow-return-left"></i> Return
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Document Whereabouts</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Current Department:</strong></p>
                    <p class="fs-5" id="whereabouts-dept"></p>
                    <hr>
                    <p class="mb-2"><strong>Current Status:</strong></p>
                    <p id="whereabouts-status-badge"></p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Quick Info</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Document Number:</strong></p>
                    <p id="quick-doc-number"></p>
                    <hr>
                    <p class="mb-2"><strong>Status:</strong></p>
                    <p id="quick-status"></p>
                    <hr>
                    <p class="mb-2"><strong>Priority:</strong></p>
                    <p id="quick-priority"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Interface -->
    <div id="scanner-interface" class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-upc-scan"></i> Scanner Ready</h5>
                </div>
                <div class="card-body text-center">
                    <div id="scanner-status" class="alert alert-success text-center">
                        <p id="status-message" class="mb-0">Scanner Ready - Point your device at a QR code</p>
                        <div id="scanning-indicator" style="display: none;" class="mt-2">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Scanning...</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <input 
                            type="text" 
                            id="scanner_input" 
                            class="form-control form-control-lg text-center" 
                            placeholder="Scan QR code or type document number here..."
                            autocomplete="off"
                            autofocus
                        >
                        <small class="text-muted d-block mt-2">Press Enter or click outside to process</small>
                    </div>

                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-secondary" id="clear-btn">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>How to use:</strong> Point your device camera at a QR code to scan automatically, or manually type the document number in the field above and press Enter.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Document Modal -->
<div class="modal fade" id="completeDocumentModal" tabindex="-1" aria-labelledby="completeDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeDocumentModalLabel">
                    <i class="bi bi-check-circle"></i> Complete Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="completeDocumentForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="complete-document-id" name="document_id" value="">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> This will mark the document as completed and automatically archive it.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Complete Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Document Modal -->
<div class="modal fade" id="returnDocumentModal" tabindex="-1" aria-labelledby="returnDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnDocumentModalLabel">
                    <i class="bi bi-arrow-return-left"></i> Return Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="returnDocumentForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="return-document-id" name="document_id" value="">
                    <div class="mb-3">
                        <label for="return-remarks" class="form-label">Remarks <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="return-remarks" name="remarks" rows="4" required placeholder="Enter remarks for returning this document..."></textarea>
                        <small class="text-muted">Please provide a reason or remarks for returning this document.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-return-left"></i> Return Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Document Success Modal -->
<div class="modal fade" id="completeSuccessModal" tabindex="-1" aria-labelledby="completeSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="completeSuccessModalLabel">
                    <i class="bi bi-check-circle-fill"></i> Success
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3 mb-2">Document Completed Successfully!</h5>
                <p class="text-muted mb-0">The document has been marked as completed and automatically archived.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="bi bi-check-circle"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Return Document Success Modal -->
<div class="modal fade" id="returnSuccessModal" tabindex="-1" aria-labelledby="returnSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="returnSuccessModalLabel">
                    <i class="bi bi-check-circle-fill"></i> Success
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3 mb-2">Document Returned Successfully!</h5>
                <p class="text-muted mb-0">The document has been returned to the previous department.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="bi bi-check-circle"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Store document ID globally for actions
let currentDocumentId = null;

// Use event delegation for complete button (works even if button is dynamically added)
document.addEventListener('click', function(e) {
    // Check if the clicked element is the complete button or inside it
    const completeBtn = e.target.closest('#complete-document-btn');
    if (completeBtn) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Complete button clicked, currentDocumentId:', currentDocumentId);
        
        // Check if document is selected
        if (!currentDocumentId) {
            alert('No document selected. Please scan a document first.');
            return false;
        }
        
        // Set the document ID in the hidden field
        const docIdField = document.getElementById('complete-document-id');
        if (docIdField) {
            docIdField.value = currentDocumentId;
            console.log('Document ID set in hidden field:', currentDocumentId);
        }
        
        // Close dropdown if open
        const dropdown = completeBtn.closest('.dropdown-menu');
        if (dropdown && dropdown.parentElement) {
            const dropdownToggle = dropdown.parentElement.querySelector('.dropdown-toggle');
            if (dropdownToggle && typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                const bsDropdown = bootstrap.Dropdown.getInstance(dropdownToggle);
                if (bsDropdown) {
                    bsDropdown.hide();
                }
            }
        }
        
        // Open the modal
        const modalElement = document.getElementById('completeDocumentModal');
        if (modalElement) {
            console.log('Opening complete modal');
            // Use Bootstrap 5 modal
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (!modal) {
                    modal = new bootstrap.Modal(modalElement);
                }
                modal.show();
            } else {
                // Fallback: show modal manually
                console.log('Bootstrap not available, using fallback');
                modalElement.classList.add('show');
                modalElement.style.display = 'block';
                modalElement.setAttribute('aria-hidden', 'false');
                modalElement.setAttribute('aria-modal', 'true');
                document.body.classList.add('modal-open');
                
                // Remove existing backdrop if any
                const existingBackdrop = document.querySelector('.modal-backdrop');
                if (existingBackdrop) {
                    existingBackdrop.remove();
                }
                
                // Create backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
        } else {
            console.error('Complete modal element not found!');
            alert('Error: Complete modal not found. Please refresh the page.');
        }
    }
});

// Use event delegation for return button (works even if button is dynamically added)
document.addEventListener('click', function(e) {
    // Check if the clicked element is the return button or inside it
    const returnBtn = e.target.closest('#return-document-btn');
    if (returnBtn) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Return button clicked, currentDocumentId:', currentDocumentId);
        
        // Check if document is selected
        if (!currentDocumentId) {
            alert('No document selected. Please scan a document first.');
            return false;
        }
        
        // Set the document ID in the hidden field
        const docIdField = document.getElementById('return-document-id');
        if (docIdField) {
            docIdField.value = currentDocumentId;
            console.log('Document ID set in hidden field:', currentDocumentId);
        }
        
        // Close dropdown if open
        const dropdown = returnBtn.closest('.dropdown-menu');
        if (dropdown && dropdown.parentElement) {
            const dropdownToggle = dropdown.parentElement.querySelector('.dropdown-toggle');
            if (dropdownToggle && typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                const bsDropdown = bootstrap.Dropdown.getInstance(dropdownToggle);
                if (bsDropdown) {
                    bsDropdown.hide();
                }
            }
        }
        
        // Open the modal
        const modalElement = document.getElementById('returnDocumentModal');
        if (modalElement) {
            console.log('Opening return modal');
            // Use Bootstrap 5 modal
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (!modal) {
                    modal = new bootstrap.Modal(modalElement);
                }
                modal.show();
            } else {
                // Fallback: show modal manually
                console.log('Bootstrap not available, using fallback');
                modalElement.classList.add('show');
                modalElement.style.display = 'block';
                modalElement.setAttribute('aria-hidden', 'false');
                modalElement.setAttribute('aria-modal', 'true');
                document.body.classList.add('modal-open');
                
                // Remove existing backdrop if any
                const existingBackdrop = document.querySelector('.modal-backdrop');
                if (existingBackdrop) {
                    existingBackdrop.remove();
                }
                
                // Create backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
            
            // Focus on remarks field after modal is shown
            setTimeout(function() {
                const remarksField = document.getElementById('return-remarks');
                if (remarksField) {
                    remarksField.focus();
                }
            }, 300);
        } else {
            console.error('Return modal element not found!');
            alert('Error: Return modal not found. Please refresh the page.');
        }
    }
});

// Wait for DOM to be ready for modal initialization
document.addEventListener('DOMContentLoaded', function() {
    // Handle complete modal show event to ensure document ID is set
    const completeModal = document.getElementById('completeDocumentModal');
    if (completeModal) {
        completeModal.addEventListener('show.bs.modal', function () {
            // Ensure document ID is set when modal opens
            const docIdField = document.getElementById('complete-document-id');
            if (docIdField && currentDocumentId) {
                docIdField.value = currentDocumentId;
            }
        });
        
    }

    // Complete document form handler
    const completeForm = document.getElementById('completeDocumentForm');
    if (completeForm) {
        completeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Complete form submitted');
            
            // Get document ID from hidden field or global variable
            const docIdField = document.getElementById('complete-document-id');
            let docId = currentDocumentId; // Use global variable first (most reliable)
            
            // If hidden field has a value, prefer it
            if (docIdField && docIdField.value) {
                docId = docIdField.value;
            }
            
            // Final fallback: if still no ID, show error
            if (!docId) {
                alert('No document selected. Please scan a document first.');
                console.error('Complete document error: currentDocumentId =', currentDocumentId, 'hidden field =', docIdField ? docIdField.value : 'not found');
                return;
            }
            
            console.log('Completing document ID:', docId);
            
            // Disable submit button to prevent double submission
            const submitBtn = completeForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Completing...';
            }
            
            const formData = new FormData();
            formData.append('document_id', docId);
            formData.append('_token', '{{ csrf_token() }}');
            
            fetch('{{ route("scan.complete") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                // Parse JSON response
                return response.json().then(data => {
                    // If response is not ok, throw error with data
                    if (!response.ok) {
                        const error = new Error(data.message || 'Failed to complete document');
                        error.data = data;
                        error.status = response.status;
                        throw error;
                    }
                    return data;
                });
            })
            .then(data => {
                if (data.success) {
                    // Close the complete modal first, then show success modal when it's fully closed
                    const completeModalElement = document.getElementById('completeDocumentModal');
                    const successModalElement = document.getElementById('completeSuccessModal');
                    
                    if (completeModalElement && successModalElement) {
                        let successModalShown = false;
                        
                        // Function to show success modal
                        const showSuccessModal = () => {
                            if (successModalShown) return; // Prevent double-showing
                            successModalShown = true;
                            
                            console.log('Showing complete success modal');
                            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                try {
                                    const successModal = new bootstrap.Modal(successModalElement);
                                    successModal.show();
                                } catch (e) {
                                    console.error('Error showing success modal:', e);
                                    // Fallback to alert
                                    alert('Document marked as complete and approved successfully!\n\nThe document has been archived.');
                                    setTimeout(() => location.reload(), 500);
                                }
                            } else {
                                // Fallback to alert if Bootstrap not available
                                alert('Document marked as complete and approved successfully!\n\nThe document has been archived.');
                                setTimeout(() => location.reload(), 500);
                            }
                        };
                        
                        // Check if modal is already open and visible
                        const isModalVisible = completeModalElement.classList.contains('show') || 
                                             completeModalElement.style.display === 'block';
                        
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal && isModalVisible) {
                            const completeModal = bootstrap.Modal.getInstance(completeModalElement);
                            if (completeModal) {
                                // Listen for when modal is fully hidden
                                completeModalElement.addEventListener('hidden.bs.modal', showSuccessModal, { once: true });
                                completeModal.hide();
                                // Fallback timeout in case event doesn't fire (will be ignored if event already fired)
                                setTimeout(showSuccessModal, 1000);
                            } else {
                                // Modal instance not found, show success modal after short delay
                                setTimeout(showSuccessModal, 300);
                            }
                        } else {
                            // Modal not visible or Bootstrap not available, show success modal directly
                            setTimeout(showSuccessModal, 300);
                        }
                    } else {
                        // Fallback: if elements not found, use alert
                        console.error('Modal elements not found');
                        alert('Document marked as complete and approved successfully!\n\nThe document has been archived.');
                        setTimeout(() => location.reload(), 500);
                    }
                } else {
                    alert('Error: ' + (data.message || 'Failed to complete document.'));
                    // Re-enable submit button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Complete Document';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Handle validation errors (422)
                if (error.status === 422 && error.data && error.data.errors) {
                    let errorMessages = [];
                    for (let field in error.data.errors) {
                        errorMessages.push(error.data.errors[field].join(', '));
                    }
                    alert('Validation Error:\n' + errorMessages.join('\n'));
                } else {
                    // Handle other errors
                    alert('An error occurred while completing the document: ' + (error.message || 'Unknown error'));
                }
                
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Complete Document';
                }
            });
        });
    }

    // Handle Cancel button click explicitly for complete modal
    const cancelCompleteBtn = completeForm ? completeForm.querySelector('button[data-bs-dismiss="modal"]') : null;
    if (cancelCompleteBtn) {
        cancelCompleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close modal using Bootstrap API
            const modalElement = document.getElementById('completeDocumentModal');
            if (modalElement) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    } else {
                        const newModal = new bootstrap.Modal(modalElement);
                        newModal.hide();
                    }
                } else {
                    // Fallback: Use data attributes and classes
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            }
            
            return false;
        });
    }

    // Reset complete modal when closed (no fields to clear)

    // Handle modal show event to ensure document ID is set
    const returnModal = document.getElementById('returnDocumentModal');
    if (returnModal) {
        returnModal.addEventListener('show.bs.modal', function () {
            // Ensure document ID is set when modal opens
            const docIdField = document.getElementById('return-document-id');
            if (docIdField && currentDocumentId) {
                docIdField.value = currentDocumentId;
            }
        });
        
        // Handle when modal is shown (Bootstrap event)
        returnModal.addEventListener('shown.bs.modal', function () {
            // Focus on remarks field when modal is shown
            const remarksField = document.getElementById('return-remarks');
            if (remarksField) {
                remarksField.focus();
            }
        });
    }

    // Return document form handler
    const returnForm = document.getElementById('returnDocumentForm');
    if (returnForm) {
        returnForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Return form submitted');
            
            // Get document ID from hidden field or global variable
            const docIdField = document.getElementById('return-document-id');
            let docId = currentDocumentId; // Use global variable first (most reliable)
            
            // If hidden field has a value, prefer it
            if (docIdField && docIdField.value) {
                docId = docIdField.value;
            }
            
            // Final fallback: if still no ID, show error
            if (!docId) {
                alert('No document selected. Please scan a document first.');
                console.error('Return document error: currentDocumentId =', currentDocumentId, 'hidden field =', docIdField ? docIdField.value : 'not found');
                return;
            }
            
            console.log('Returning document ID:', docId);
            
            const remarksField = document.getElementById('return-remarks');
            const remarks = remarksField ? remarksField.value.trim() : '';
            
            if (!remarks) {
                alert('Please enter remarks for returning the document.');
                remarksField.focus();
                return;
            }
            
            // Disable submit button to prevent double submission
            const submitBtn = returnForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Returning...';
            }
            
            const formData = new FormData();
            formData.append('document_id', docId);
            formData.append('remarks', remarks);
            formData.append('_token', '{{ csrf_token() }}');
            
            fetch('{{ route("scan.return") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                // Parse JSON response
                return response.json().then(data => {
                    // If response is not ok, throw error with data
                    if (!response.ok) {
                        const error = new Error(data.message || 'Failed to return document');
                        error.data = data;
                        error.status = response.status;
                        throw error;
                    }
                    return data;
                });
            })
            .then(data => {
                if (data.success) {
                    // Clear form
                    if (remarksField) {
                        remarksField.value = '';
                    }
                    
                    // Close the return modal first, then show success modal when it's fully closed
                    const returnModalElement = document.getElementById('returnDocumentModal');
                    const successModalElement = document.getElementById('returnSuccessModal');
                    
                    if (returnModalElement && successModalElement) {
                        let successModalShown = false;
                        
                        // Function to show success modal
                        const showSuccessModal = () => {
                            if (successModalShown) return; // Prevent double-showing
                            successModalShown = true;
                            
                            console.log('Showing return success modal');
                            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                try {
                                    const successModal = new bootstrap.Modal(successModalElement);
                                    successModal.show();
                                } catch (e) {
                                    console.error('Error showing success modal:', e);
                                    // Fallback to alert
                                    alert('Document returned successfully!');
                                    setTimeout(() => location.reload(), 500);
                                }
                            } else {
                                // Fallback to alert if Bootstrap not available
                                alert('Document returned successfully!');
                                setTimeout(() => location.reload(), 500);
                            }
                        };
                        
                        // Check if modal is already open and visible
                        const isModalVisible = returnModalElement.classList.contains('show') || 
                                             returnModalElement.style.display === 'block';
                        
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal && isModalVisible) {
                            const returnModal = bootstrap.Modal.getInstance(returnModalElement);
                            if (returnModal) {
                                // Listen for when modal is fully hidden
                                returnModalElement.addEventListener('hidden.bs.modal', showSuccessModal, { once: true });
                                returnModal.hide();
                                // Fallback timeout in case event doesn't fire (will be ignored if event already fired)
                                setTimeout(showSuccessModal, 1000);
                            } else {
                                // Modal instance not found, show success modal after short delay
                                setTimeout(showSuccessModal, 300);
                            }
                        } else {
                            // Modal not visible or Bootstrap not available, show success modal directly
                            setTimeout(showSuccessModal, 300);
                        }
                    } else {
                        // Fallback: if elements not found, use alert
                        console.error('Modal elements not found');
                        alert('Document returned successfully!');
                        setTimeout(() => location.reload(), 500);
                    }
                } else {
                    alert('Error: ' + (data.message || 'Failed to return document.'));
                    // Re-enable submit button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-arrow-return-left"></i> Return Document';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Handle validation errors (422)
                if (error.status === 422 && error.data && error.data.errors) {
                    let errorMessages = [];
                    for (let field in error.data.errors) {
                        errorMessages.push(error.data.errors[field].join(', '));
                    }
                    alert('Validation Error:\n' + errorMessages.join('\n'));
                } else {
                    // Handle other errors
                    alert('An error occurred while returning the document: ' + (error.message || 'Unknown error'));
                }
                
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-arrow-return-left"></i> Return Document';
                }
            });
        });
    }

    // Handle Cancel button click explicitly
    const cancelReturnBtn = returnForm ? returnForm.querySelector('button[data-bs-dismiss="modal"]') : null;
    if (cancelReturnBtn) {
        cancelReturnBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close modal using Bootstrap API
            const modalElement = document.getElementById('returnDocumentModal');
            if (modalElement) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    } else {
                        const newModal = new bootstrap.Modal(modalElement);
                        newModal.hide();
                    }
                } else {
                    // Fallback: Use data attributes and classes
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            }
            
            // Clear form fields
            const remarksField = document.getElementById('return-remarks');
            if (remarksField) {
                remarksField.value = '';
            }
            
            return false;
        });
    }

    // Reset return modal when closed
    if (returnModal) {
        returnModal.addEventListener('hidden.bs.modal', function () {
            const remarksField = document.getElementById('return-remarks');
            if (remarksField) {
                remarksField.value = '';
            }
        });
        
        // Also handle when modal is closed via backdrop click or ESC
        returnModal.addEventListener('hide.bs.modal', function () {
            const remarksField = document.getElementById('return-remarks');
            if (remarksField) {
                remarksField.value = '';
            }
        });
    }

    // Auto-reload on success modal close
    const completeSuccessModal = document.getElementById('completeSuccessModal');
    if (completeSuccessModal) {
        completeSuccessModal.addEventListener('hidden.bs.modal', function () {
            location.reload();
        });
    }

    const returnSuccessModal = document.getElementById('returnSuccessModal');
    if (returnSuccessModal) {
        returnSuccessModal.addEventListener('hidden.bs.modal', function () {
            location.reload();
        });
    }
});

// Display document information
function displayDocument(doc, detailsUrl) {
    // Store document ID for actions
    currentDocumentId = doc.id;
    
    // Populate document details
    document.getElementById('doc-title').textContent = doc.title;
    document.getElementById('doc-number').textContent = doc.document_number;
    document.getElementById('doc-type').textContent = doc.document_type || 'N/A';
    document.getElementById('doc-department').textContent = doc.department;
    document.getElementById('doc-last-location').textContent = doc.last_location || 'N/A';
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
        'Return': 'danger',
        'Completed': 'primary',
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
    
    // Reset document ID
    currentDocumentId = null;
    
    // Refocus scanner input (after a short delay to ensure everything is reset)
    setTimeout(() => {
        document.getElementById('scanner_input')?.focus();
    }, 100);
}

// Scanner functionality
let isProcessing = false;
let enterKeyPressed = false;

document.addEventListener('DOMContentLoaded', function() {
    const scannerInput = document.getElementById('scanner_input');
    const clearBtn = document.getElementById('clear-btn');
    const statusDiv = document.getElementById('scanner-status');
    const statusMsg = document.getElementById('status-message');
    const scanningIndicator = document.getElementById('scanning-indicator');

    if (scannerInput) {
        scannerInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !isProcessing) {
                e.preventDefault();
                enterKeyPressed = true;
                processScan();
                // Reset flag after a short delay to allow blur event to check it
                setTimeout(() => {
                    enterKeyPressed = false;
                }, 200);
            }
        });

        scannerInput.addEventListener('blur', function() {
            // Don't trigger scan if Enter was just pressed or if processing
            if (scannerInput.value.trim() && !isProcessing && !enterKeyPressed) {
                processScan();
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            scannerInput.value = '';
            scannerInput.focus();
            statusMsg.textContent = 'Scanner Ready - Point your device at a QR code';
        });
    }

    function processScan() {
        let documentNumber = scannerInput.value.trim();
        
        // Extract document code from URL if scanned value is a URL
        // Handles URLs like: http://127.0.0.1:8000/scan?document=DOC-202511-0009
        if (documentNumber.includes('?document=') || documentNumber.includes('&document=')) {
            try {
                // Try to parse as URL
                const url = new URL(documentNumber);
                documentNumber = url.searchParams.get('document') || documentNumber;
            } catch (e) {
                // If URL parsing fails, try regex extraction
                const match = documentNumber.match(/[?&]document=([^&]+)/);
                if (match && match[1]) {
                    documentNumber = decodeURIComponent(match[1]);
                }
            }
        }
        
        // Also handle if the scanned value is just a URL path with query string
        if (!documentNumber && scannerInput.value.trim().includes('document=')) {
            const match = scannerInput.value.trim().match(/document=([^&\s]+)/);
            if (match && match[1]) {
                documentNumber = decodeURIComponent(match[1]);
            }
        }
        
        if (!documentNumber) {
            return;
        }

        if (isProcessing) {
            return;
        }

        isProcessing = true;
        statusDiv.className = 'alert alert-info text-center';
        statusMsg.textContent = 'Processing scan...';
        scanningIndicator.style.display = 'block';

        fetch('{{ route("scan.process") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                document_number: documentNumber
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Failed to process scan');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Clear input after successful scan to prevent duplicate processing
                scannerInput.value = '';
                statusDiv.className = 'alert alert-success text-center';
                statusMsg.textContent = data.message || 'Document scanned successfully!';
                scanningIndicator.style.display = 'none';
                
                enterKeyPressed = false; // Reset flag on success
                
                // Display document information (include scanner info if available)
                if (data.scanner) {
                    data.document.scanner = data.scanner;
                }
                displayDocument(data.document, data.redirect_url);
                
                // Hide scanner interface
                document.getElementById('scanner-interface').style.display = 'none';
                statusDiv.style.display = 'none';
                
                // Reset processing flag
                isProcessing = false;
            } else {
                throw new Error(data.message || 'Scan failed');
            }
        })
        .catch(error => {
            console.error('Scan error:', error);
            statusDiv.className = 'alert alert-danger text-center';
            statusMsg.textContent = 'Error: ' + (error.message || 'Failed to process scan');
            scanningIndicator.style.display = 'none';
            isProcessing = false;
            enterKeyPressed = false; // Reset flag on error
            
            // Refocus input after error
            setTimeout(() => {
                scannerInput.focus();
            }, 1000);
        });
    }

    // Auto-focus scanner input on page load
    if (scannerInput) {
        setTimeout(() => {
            scannerInput.focus();
        }, 500);
    }
});
</script>
@endpush

@endsection
