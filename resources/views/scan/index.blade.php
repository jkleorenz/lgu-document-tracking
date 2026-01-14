@extends('layouts.app')

@section('title', 'QR Code Scanner')

@section('content')
<style>
    /* Scanner Input Field Styling - Enhanced Visual Hierarchy */
    #scanner_input {
        font-size: 1.3rem;
        font-weight: 600;
        border: 3px solid #0d6efd;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
        background-color: #ffffff;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
    }
    
    #scanner_input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.3rem rgba(13, 110, 253, 0.25), 0 4px 12px rgba(13, 110, 253, 0.15);
        transform: scale(1.01);
        outline: none;
    }
    
    #scanner_input::placeholder {
        color: #6c757d;
        font-weight: 400;
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
    
    /* Multi-Scan Mode Toggle Card */
    .mode-toggle-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }
    
    /* Toggle Switch Styling */
    .mode-toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
    }
    
    .mode-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 30px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
    }
    
    input:checked + .toggle-slider:before {
        transform: translateX(30px);
    }
    
    .toggle-label {
        font-size: 0.9rem;
        font-weight: 500;
        color: #495057;
        margin-left: 12px;
    }
    
    /* Checkmark Animation */
    @keyframes checkmark-scale {
        0% {
            transform: scale(0.8);
            opacity: 0;
        }
        50% {
            transform: scale(1.15);
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    @keyframes checkmark-fade {
        0% {
            opacity: 1;
        }
        100% {
            opacity: 0;
        }
    }
    
    .checkmark-indicator {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2rem;
        color: #28a745;
        animation: checkmark-scale 0.3s ease-out, checkmark-fade 0.8s ease-in 0.5s forwards;
        display: none; /* Hidden by default */
    }

    .received-label {
        position: absolute;
        right: 60px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.85rem;
        color: #6c757d;
        animation: checkmark-fade 0.8s ease-in 0.5s forwards;
        opacity: 1;
        display: none; /* Hidden by default */
    }
    
    /* Scanner input wrapper for relative positioning */
    .scanner-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    /* Success animation for input field */
    @keyframes success-pulse {
        0%, 100% {
            border-color: #28a745;
            box-shadow: 0 0 0 0.3rem rgba(40, 167, 69, 0.25);
        }
        50% {
            border-color: #20c997;
            box-shadow: 0 0 0 0.4rem rgba(40, 167, 69, 0.35);
        }
    }
    
    #scanner_input.success-feedback {
        animation: success-pulse 0.6s ease-out;
    }
    
    /* Error animation for input field */
    @keyframes error-shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    #scanner_input.error-feedback {
        border-color: #dc3545;
        animation: error-shake 0.5s ease-out;
    }
    
    /* Session Statistics */
    .inline-session-stats {
        display: flex;
        gap: 20px;
        font-size: 0.9rem;
        color: #0c5460;
        font-weight: 500;
    }
    
    .inline-stats-text {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .stats-number {
        color: #0d6efd;
        font-weight: 700;
        font-size: 1rem;
    }
    
    /* Mode Confirmation Banner */
    .mode-confirmation {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #0d6efd;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        font-size: 0.9rem;
        font-weight: 500;
        animation: slide-in 0.3s ease-out, slide-out 1s ease-in 0.8s forwards;
        z-index: 1050;
        display: none;
    }
    
    .mode-confirmation.show {
        display: block;
    }
    
    @keyframes slide-in {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slide-out {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    /* Warning/Error Indicators */
    .brief-indicator {
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 12px 16px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 500;
        animation: slide-in 0.3s ease-out, slide-out 1.5s ease-in 1.2s forwards;
        z-index: 1049;
        display: none;
    }
    
    .brief-indicator.show {
        display: block;
    }
    
    .brief-indicator.success {
        background: #28a745;
        color: white;
    }
    
    .brief-indicator.warning {
        background: #ffc107;
        color: #333;
    }
    
    .brief-indicator.error {
        background: #dc3545;
        color: white;
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
    
    /* Scanned Documents List Styling */
    .scanned-document-item {
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    
    .scanned-document-item:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
        transform: translateX(2px);
    }
    
    .scanned-document-item .document-title {
        font-weight: 600;
        color: #212529;
    }
    
    .scanned-document-item .document-number {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .scanned-document-item .document-status {
        font-size: 0.8rem;
    }
    
    /* Enhanced Status Alert Styling */
    #scanner-status {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Help tooltip button styling */
    .btn-link[data-bs-toggle="tooltip"] {
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .btn-link[data-bs-toggle="tooltip"]:hover {
        transform: scale(1.1);
    }
    
    .btn-link[data-bs-toggle="tooltip"] .bi-question-circle {
        transition: color 0.2s ease;
    }
    
    .btn-link[data-bs-toggle="tooltip"]:hover .bi-question-circle {
        color: #0d6efd !important;
    }
    
    /* Manual tooltip styling (fallback) */
    .manual-tooltip {
        pointer-events: none;
        animation: fadeIn 0.2s ease-out;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
            <!-- Mode Toggle Card -->
            <div class="card mode-toggle-card mb-3" id="mode-toggle-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <label class="mode-toggle-switch">
                            <input type="checkbox" id="scan-mode-toggle">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label" id="mode-label">Turn on Multi-Scan Mode</span>
                    </div>
                    <!-- Inline Session Stats (Multi-Scan Only) -->
                    <div class="inline-session-stats" id="inline-session-stats" style="display: none;">
                        <span class="inline-stats-text">
                            <span class="stats-number" id="inline-stats-received">0</span> received
                        </span>
                        <span class="inline-stats-text">
                            <span class="stats-number" id="inline-stats-errors">0</span> errors
                        </span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-upc-scan"></i> Scanner Ready</h5>
                </div>
                <div class="card-body text-center">
                    <!-- Status alert - only shown when processing or error -->
                    <div id="scanner-status" class="alert text-center" style="display: none;">
                        <p id="status-message" class="mb-0"></p>
                        <div id="scanning-indicator" style="display: none;" class="mt-2">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Scanning...</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="scanner-input-wrapper">
                            <input 
                                type="text" 
                                id="scanner_input" 
                                class="form-control form-control-lg text-center" 
                                placeholder="Scan QR code or type document number here..."
                                autocomplete="off"
                                autofocus
                            >
                            <i class="bi bi-check-circle-fill checkmark-indicator" id="checkmark-indicator"></i>
                            <div class="received-label" id="received-label">Received</div>
                        </div>
                        <div class="position-relative mt-2 text-center" style="width: 100%;">
                            <small class="text-muted">Press Enter or click outside to process</small>
                            <!-- Help tooltip icon - positioned absolutely to not affect centering -->
                            <button type="button" class="btn btn-link p-0" id="scan-mode-help-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%);">
                                <i class="bi bi-question-circle text-muted" style="font-size: 1.1rem;"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <button type="button" class="btn btn-secondary" id="clear-btn">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                        <button type="button" class="btn btn-info" id="view-last-btn" style="display: none;">
                            <i class="bi bi-list-ul"></i> View Scanned Documents
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mode Confirmation Banner -->
    <div class="mode-confirmation" id="mode-confirmation"></div>

    <!-- Brief Indicator (Warnings/Errors in Multi-Scan) -->
    <div class="brief-indicator" id="brief-indicator"></div>
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
                    <div class="mb-3">
                        <label for="return-department" class="form-label">
                            <i class="bi bi-building"></i> Return to Department (Optional)
                        </label>
                        <select class="form-select" id="return-department" name="return_department">
                            <option value="">-- Auto-detect Previous Department --</option>
                            @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->display_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Leave blank to automatically return to the previous department that handled this document.</small>
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

<!-- Scanned Documents List Modal -->
<div class="modal fade" id="scannedDocumentsModal" tabindex="-1" aria-labelledby="scannedDocumentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="scannedDocumentsModalLabel">
                    <i class="bi bi-list-ul"></i> Scanned Documents
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <div id="scanned-documents-list-container">
                    <div id="scanned-documents-empty" class="text-center text-muted py-4" style="display: none;">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-3">No documents scanned yet in this session.</p>
                    </div>
                    <div id="scanned-documents-list" class="list-group">
                        <!-- Documents will be populated here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="bi bi-check-circle"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ==============================================
// Multi-Scan Mode: Global State Management
// ==============================================

let currentDocumentId = null;
let multiScanMode = localStorage.getItem('multiScanMode') === 'true' || false;
let isProcessing = false;
let enterKeyPressed = false;
let lastScannedDocId = null;
let lastScanTime = 0;
let scanCount = 0;
let rapidScanCount = 0;
let rapidScanTime = 0;
let errorCount = 0;
let scannedDocuments = []; // Array to store all scanned documents in multi-scan mode

// ==============================================
// Multi-Scan Helper Functions
// ==============================================

function showModeConfirmation(message) {
    const banner = document.getElementById('mode-confirmation');
    banner.textContent = message;
    banner.classList.add('show');
    setTimeout(() => {
        banner.classList.remove('show');
    }, 1500);
}

function showBriefIndicator(message, type = 'success') {
    const indicator = document.getElementById('brief-indicator');
    indicator.textContent = message;
    indicator.className = `brief-indicator show ${type}`;
    setTimeout(() => {
        indicator.classList.remove('show');
    }, 1500);
}

function vibrate() {
    if (navigator.vibrate) {
        navigator.vibrate(50);
    }
}

function updateSessionStats() {
    document.getElementById('inline-stats-received').textContent = scanCount;
    document.getElementById('inline-stats-errors').textContent = errorCount;
}

function showCheckmark() {
    const indicator = document.getElementById('checkmark-indicator');
    const label = document.getElementById('received-label');
    indicator.style.display = 'block';
    label.style.display = 'block';
    
    setTimeout(() => {
        indicator.style.display = 'none';
        label.style.display = 'none';
    }, 800);
}

function clearScannerInput() {
    const input = document.getElementById('scanner_input');
    setTimeout(() => {
        input.value = '';
        input.focus();
    }, 500);
}

// Function to show scanned documents modal
function showScannedDocumentsModal() {
    const modal = document.getElementById('scannedDocumentsModal');
    const listContainer = document.getElementById('scanned-documents-list');
    const emptyMessage = document.getElementById('scanned-documents-empty');
    
    // Sort documents by scan time (newest first)
    const sortedDocuments = [...scannedDocuments].sort((a, b) => b.scan_time - a.scan_time);
    
    // Clear previous content
    listContainer.innerHTML = '';
    
    if (sortedDocuments.length === 0) {
        emptyMessage.style.display = 'block';
        listContainer.style.display = 'none';
    } else {
        emptyMessage.style.display = 'none';
        listContainer.style.display = 'block';
        
        // Status color mapping
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
        
        sortedDocuments.forEach((doc, index) => {
            const statusColor = statusColors[doc.status] || 'secondary';
            const scanTime = new Date(doc.scan_time).toLocaleTimeString();
            
            const listItem = document.createElement('a');
            listItem.href = '#';
            listItem.className = 'list-group-item list-group-item-action scanned-document-item';
            listItem.onclick = function(e) {
                e.preventDefault();
                window.open(doc.redirect_url, '_blank');
            };
            
            listItem.innerHTML = `
                <div class="d-flex w-100 justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 document-title">${doc.title}</h6>
                        <p class="mb-1 document-number"><i class="bi bi-file-earmark-text"></i> ${doc.document_number}</p>
                        <small class="document-status">
                            <span class="badge bg-${statusColor}">${doc.status}</span>
                        </small>
                    </div>
                    <small class="text-muted ms-3">${scanTime}</small>
                </div>
            `;
            
            listContainer.appendChild(listItem);
        });
    }
    
    // Show modal using Bootstrap
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Get existing instance or create new one
        let bsModal = bootstrap.Modal.getInstance(modal);
        if (!bsModal) {
            bsModal = new bootstrap.Modal(modal);
        }
        bsModal.show();
    } else {
        // Fallback
        modal.classList.add('show');
        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        
        // Add backdrop for fallback
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'scanned-documents-backdrop';
        document.body.appendChild(backdrop);
    }
}

// Function to close scanned documents modal
function closeScannedDocumentsModal() {
    const modal = document.getElementById('scannedDocumentsModal');
    if (!modal) return;
    
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        } else {
            // Fallback close
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            const backdrop = document.getElementById('scanned-documents-backdrop');
            if (backdrop) backdrop.remove();
        }
    } else {
        // Fallback close
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        const backdrop = document.getElementById('scanned-documents-backdrop');
        if (backdrop) backdrop.remove();
    }
}

// ==============================================
// Mode Toggle Logic
// ==============================================

document.addEventListener('DOMContentLoaded', function() {
    const modeToggle = document.getElementById('scan-mode-toggle');
    const modeLabel = document.getElementById('mode-label');
    const modeCard = document.getElementById('mode-toggle-card');
    const inlineSessionStats = document.getElementById('inline-session-stats');
    const clearBtn = document.getElementById('clear-btn');
    const viewLastBtn = document.getElementById('view-last-btn');
    const scannerInput = document.getElementById('scanner_input');

    // Hide checkmark and received label on page load
    const checkmarkIndicator = document.getElementById('checkmark-indicator');
    const receivedLabel = document.getElementById('received-label');
    if (checkmarkIndicator) checkmarkIndicator.style.display = 'none';
    if (receivedLabel) receivedLabel.style.display = 'none';

    // Initialize mode from localStorage
    modeToggle.checked = multiScanMode;
    updateModeUI();

    // Handle mode toggle
    modeToggle.addEventListener('change', function() {
        multiScanMode = this.checked;
        localStorage.setItem('multiScanMode', multiScanMode);
        
        // Reset scanner state
        scannerInput.value = '';
        scannerInput.disabled = false;
        isProcessing = false;
        
        // Reset stats in single-scan mode
        if (!multiScanMode) {
            scanCount = 0;
            errorCount = 0;
            inlineSessionStats.style.display = 'none';
            viewLastBtn.style.display = 'none';
        } else {
            scanCount = 0;
            errorCount = 0;
            inlineSessionStats.style.display = 'flex';
            updateSessionStats();
        }
        
        updateModeUI();
        scannerInput.focus();
    });

    function updateModeUI() {
        if (multiScanMode) {
            modeLabel.textContent = 'Multi-Scan • On';
            modeLabel.classList.add('active');
            modeCard.classList.add('multi-scan-active');
            inlineSessionStats.style.display = 'flex';
            viewLastBtn.style.display = 'inline-block';
        } else {
            modeLabel.textContent = 'Turn on Multi-Scan Mode';
            modeLabel.classList.remove('active');
            modeCard.classList.remove('multi-scan-active');
            inlineSessionStats.style.display = 'none';
            viewLastBtn.style.display = 'none';
        }
    }

    // ==============================================
    // Scanner Input Handlers
    // ==============================================

    scannerInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !isProcessing) {
            e.preventDefault();
            enterKeyPressed = true;
            processScan();
            setTimeout(() => {
                enterKeyPressed = false;
            }, 200);
        }
    });

    scannerInput.addEventListener('blur', function() {
        if (scannerInput.value.trim() && !isProcessing && !enterKeyPressed) {
            processScan();
        }
    });

    scannerInput.addEventListener('focus', function() {
        // Scanner input focused
    });

    clearBtn.addEventListener('click', function() {
        scannerInput.value = '';
        scannerInput.focus();
    });

    viewLastBtn.addEventListener('click', function() {
        showScannedDocumentsModal();
    });

    // ==============================================
    // Main Scanning Logic
    // ==============================================

    function processScan() {
        let scannedValue = scannerInput.value.trim();
        let documentNumber = scannedValue;
        
        // Extract document code from URL if needed
        if (scannedValue.includes('document=')) {
            try {
                if (scannedValue.includes('://')) {
                    let url = new URL(scannedValue);
                    const extractedCode = url.searchParams.get('document');
                    if (extractedCode) {
                        documentNumber = extractedCode;
                    }
                } else if (scannedValue.includes('?') || scannedValue.includes('&')) {
                    const match = scannedValue.match(/document=([^&\s#]+)/);
                    if (match && match[1]) {
                        documentNumber = decodeURIComponent(match[1]);
                    }
                }
            } catch (e) {
                const match = scannedValue.match(/document=([^&\s#]+)/);
                if (match && match[1]) {
                    documentNumber = decodeURIComponent(match[1]);
                }
            }
        }
        
        if (!documentNumber) {
            return;
        }

        if (isProcessing) {
            return;
        }

        // ==============================================
        // Duplicate Detection (5-second window)
        // ==============================================
        
        if (multiScanMode && lastScannedDocId === documentNumber && Date.now() - lastScanTime < 5000) {
            showBriefIndicator('Duplicate scan detected', 'warning');
            clearScannerInput();
            return;
        }

        // ==============================================
        // Rapid Scan Detection (3+ scans in 2 seconds)
        // ==============================================
        
        if (multiScanMode) {
            const now = Date.now();
            if (now - rapidScanTime < 2000) {
                rapidScanCount++;
            } else {
                rapidScanCount = 1;
                rapidScanTime = now;
            }

            if (rapidScanCount >= 3) {
                showBriefIndicator('Scanning too rapidly—pause 1 second', 'warning');
                isProcessing = true;
                setTimeout(() => {
                    isProcessing = false;
                    if (scannerInput.value.trim()) {
                        processScan();
                    }
                }, 1000);
                return;
            }
        }

        isProcessing = true;
        scannerInput.value = documentNumber;
        
        const statusDiv = document.getElementById('scanner-status');
        const statusMsg = document.getElementById('status-message');
        const scanningIndicator = document.getElementById('scanning-indicator');

        // Show status alert when processing
        statusDiv.style.display = 'block';
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
                // Remember last scanned document
                lastScannedDocId = documentNumber;
                lastScanTime = Date.now();
                currentDocumentId = data.document.id;
                scanCount++;
                updateSessionStats();
                
                // Hide status alert on success in multi-scan mode, show briefly in single-scan
                if (multiScanMode) {
                    statusDiv.style.display = 'none';
                } else {
                    statusDiv.style.display = 'block';
                    statusDiv.className = 'alert alert-success text-center';
                    statusMsg.textContent = data.message || 'Document scanned successfully!';
                }
                scanningIndicator.style.display = 'none';
                enterKeyPressed = false;
                
                // Add success feedback animation to input
                scannerInput.classList.add('success-feedback');
                setTimeout(() => {
                    scannerInput.classList.remove('success-feedback');
                }, 600);
                
                if (multiScanMode) {
                    // Multi-Scan: Silent acknowledgment
                    // Store document info in scannedDocuments array
                    scannedDocuments.push({
                        id: data.document.id,
                        document_number: data.document.document_number || documentNumber,
                        title: data.document.title || 'Untitled Document',
                        status: data.document.status || 'Unknown',
                        scan_time: Date.now(),
                        redirect_url: data.redirect_url || '/documents/' + data.document.id
                    });
                    
                    showCheckmark();
                    vibrate();
                    clearScannerInput();
                    isProcessing = false;
                } else {
                    // Single-Scan: Show full details
                    if (data.scanner) {
                        data.document.scanner = data.scanner;
                    }
                    displayDocument(data.document, data.redirect_url);
                    document.getElementById('scanner-interface').style.display = 'none';
                    statusDiv.style.display = 'none';
                    isProcessing = false;
                }
            } else {
                throw new Error(data.message || 'Scan failed');
            }
        })
        .catch(error => {
            console.error('Scan error:', error);
            errorCount++;
            updateSessionStats();
            
            // Show error status
            statusDiv.style.display = 'block';
            statusDiv.className = 'alert alert-danger text-center';
            statusMsg.textContent = 'Error: ' + (error.message || 'Failed to process scan');
            scanningIndicator.style.display = 'none';
            isProcessing = false;
            enterKeyPressed = false;
            
            // Add error feedback animation to input
            scannerInput.classList.add('error-feedback');
            setTimeout(() => {
                scannerInput.classList.remove('error-feedback');
            }, 500);
            
            if (multiScanMode) {
                showBriefIndicator(error.message.includes('not found') ? 'Not found' : 'Connection lost', 'error');
                // Hide status alert after brief display in multi-scan mode
                setTimeout(() => {
                    statusDiv.style.display = 'none';
                }, 2000);
                clearScannerInput();
            } else {
                // Keep error visible in single-scan mode
                setTimeout(() => {
                    scannerInput.focus();
                }, 1000);
            }
        });
    }

    // Auto-focus scanner on page load
    setTimeout(() => {
        scannerInput.focus();
    }, 500);
});

// ==============================================
// Single-Scan Mode Document Display
// ==============================================

function displayDocument(doc, detailsUrl) {
    currentDocumentId = doc.id;
    
    document.getElementById('doc-title').textContent = doc.title;
    document.getElementById('doc-number').textContent = doc.document_number;
    document.getElementById('doc-type').textContent = doc.document_type || 'N/A';
    document.getElementById('doc-department').textContent = doc.department;
    document.getElementById('doc-last-location').textContent = doc.last_location || 'N/A';
    document.getElementById('doc-creator').textContent = doc.created_by;
    document.getElementById('doc-created').textContent = doc.created_at;
    document.getElementById('doc-description').textContent = doc.description || 'No description provided';
    document.getElementById('view-full-details').href = detailsUrl;
    
    document.getElementById('doc-location').innerHTML = `<strong>${doc.department}</strong>`;
    
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
    
    if (doc.is_priority) {
        document.getElementById('doc-priority-badge').style.display = 'inline-block';
    } else {
        document.getElementById('doc-priority-badge').style.display = 'none';
    }
    
    document.getElementById('whereabouts-dept').textContent = doc.department;
    document.getElementById('whereabouts-status-badge').innerHTML = statusBadge;
    
    document.getElementById('quick-doc-number').textContent = doc.document_number;
    document.getElementById('quick-status').innerHTML = statusBadge;
    document.getElementById('quick-priority').innerHTML = doc.is_priority 
        ? '<span class="badge badge-priority">PRIORITY</span>' 
        : '<span class="text-muted">Normal</span>';
    
    document.getElementById('document-result').style.display = 'flex';
    document.getElementById('document-result').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetScanner() {
    document.getElementById('document-result').style.display = 'none';
    document.getElementById('scanner-interface').style.display = 'flex';
    
    const scannerInput = document.getElementById('scanner_input');
    scannerInput.value = '';
    
    const statusDiv = document.getElementById('scanner-status');
    const statusMsg = document.getElementById('status-message');
    const scanningIndicator = document.getElementById('scanning-indicator');
    
    // Hide status alert when ready (clean interface)
    statusDiv.style.display = 'none';
    statusDiv.className = 'alert alert-success text-center';
    statusMsg.textContent = 'Scanner Ready - Point your device at a QR code';
    scanningIndicator.style.display = 'none';
    
    // Hide checkmark and received label
    const checkmarkIndicator = document.getElementById('checkmark-indicator');
    const receivedLabel = document.getElementById('received-label');
    if (checkmarkIndicator) checkmarkIndicator.style.display = 'none';
    if (receivedLabel) receivedLabel.style.display = 'none';
    
    isProcessing = false;
    currentDocumentId = null;
    
    setTimeout(() => {
        scannerInput?.focus();
    }, 100);
}

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
            
            // Get the selected return department (optional)
            const returnDeptField = document.getElementById('return-department');
            const returnDept = returnDeptField ? returnDeptField.value.trim() : '';
            
            // Disable submit button to prevent double submission
            const submitBtn = returnForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Returning...';
            }
            
            const formData = new FormData();
            formData.append('document_id', docId);
            formData.append('remarks', remarks);
            if (returnDept) {
                formData.append('return_department', returnDept);
            }
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
            const returnDeptField = document.getElementById('return-department');
            if (returnDeptField) {
                returnDeptField.value = '';
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
            const returnDeptField = document.getElementById('return-department');
            if (returnDeptField) {
                returnDeptField.value = '';
            }
        });
        
        // Also handle when modal is closed via backdrop click or ESC
        returnModal.addEventListener('hide.bs.modal', function () {
            const remarksField = document.getElementById('return-remarks');
            if (remarksField) {
                remarksField.value = '';
            }
            const returnDeptField = document.getElementById('return-department');
            if (returnDeptField) {
                returnDeptField.value = '';
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

    // Handle scanned documents modal close buttons
    const scannedDocumentsModal = document.getElementById('scannedDocumentsModal');
    if (scannedDocumentsModal) {
        // Close button (X) in header
        const closeBtn = scannedDocumentsModal.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeScannedDocumentsModal();
            });
        }
        
        // OK button in footer
        const okBtn = scannedDocumentsModal.querySelector('.modal-footer .btn');
        if (okBtn) {
            okBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeScannedDocumentsModal();
            });
        }
        
        // Also handle backdrop click to close
        scannedDocumentsModal.addEventListener('click', function(e) {
            if (e.target === scannedDocumentsModal) {
                closeScannedDocumentsModal();
            }
        });
        
        // Handle ESC key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && scannedDocumentsModal.classList.contains('show')) {
                closeScannedDocumentsModal();
            }
        });
    }

    // Initialize Bootstrap tooltips - with delay to ensure Bootstrap is loaded
    function initializeTooltips() {
        const helpBtn = document.getElementById('scan-mode-help-btn');
        if (!helpBtn) {
            // Retry if element not found yet
            setTimeout(initializeTooltips, 50);
            return;
        }
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            try {
                // Destroy existing tooltip if any
                const existingTooltip = bootstrap.Tooltip.getInstance(helpBtn);
                if (existingTooltip) {
                    existingTooltip.dispose();
                }
                
                // Create new tooltip with HTML content
                const tooltipContent = '<div style="text-align: left; padding: 4px;"><strong>Scanning Modes:</strong><br>• <strong>Multi-Scan:</strong> Scans continuously in the background<br>• <strong>Single-Scan:</strong> Shows details after every scan</div>';
                
                // Set title attribute first (Bootstrap reads from it)
                helpBtn.setAttribute('data-bs-original-title', tooltipContent);
                
                const tooltip = new bootstrap.Tooltip(helpBtn, {
                    html: true,
                    placement: 'top',
                    trigger: 'hover focus',
                    boundary: 'viewport'
                });
                
                // Verify tooltip was created
                if (bootstrap.Tooltip.getInstance(helpBtn)) {
                    console.log('Bootstrap tooltip initialized successfully');
                } else {
                    throw new Error('Tooltip instance not created');
                }
            } catch (error) {
                console.error('Error initializing Bootstrap tooltip:', error);
                // Will fall back to manual tooltip below
            }
        } else {
            // Retry after a short delay if Bootstrap is not yet loaded
            setTimeout(initializeTooltips, 100);
        }
    }
    
    // Initialize tooltips after DOM is ready
    setTimeout(initializeTooltips, 200);
    
    // Also initialize on window load as fallback
    window.addEventListener('load', function() {
        setTimeout(initializeTooltips, 300);
    });
    
    // Fallback: Manual tooltip on hover if Bootstrap fails (check after initialization)
    setTimeout(function() {
        const helpBtn = document.getElementById('scan-mode-help-btn');
        if (helpBtn) {
            // Check if Bootstrap tooltip is working
            let tooltipWorking = false;
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipInstance = bootstrap.Tooltip.getInstance(helpBtn);
                tooltipWorking = tooltipInstance !== null;
            }
            
            // Only add manual fallback if Bootstrap tooltip didn't work
            if (!tooltipWorking) {
                let tooltipElement = null;
                
                helpBtn.addEventListener('mouseenter', function(e) {
                    if (tooltipElement) return; // Already showing
                    
                    // Create manual tooltip
                    tooltipElement = document.createElement('div');
                    tooltipElement.className = 'manual-tooltip';
                    tooltipElement.innerHTML = '<div style="text-align: left; padding: 8px; background: #333; color: white; border-radius: 4px; font-size: 0.875rem; max-width: 250px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 1060;"><strong>Scanning Modes:</strong><br>• <strong>Multi-Scan:</strong> Scans continuously in the background<br>• <strong>Single-Scan:</strong> Shows details after every scan</div>';
                    tooltipElement.style.position = 'fixed';
                    tooltipElement.style.zIndex = '1060';
                    document.body.appendChild(tooltipElement);
                    
                    const rect = helpBtn.getBoundingClientRect();
                    const tooltipRect = tooltipElement.getBoundingClientRect();
                    tooltipElement.style.top = (rect.top - tooltipRect.height - 8) + 'px';
                    tooltipElement.style.left = (rect.left + rect.width / 2 - tooltipRect.width / 2) + 'px';
                });
                
                helpBtn.addEventListener('mouseleave', function() {
                    if (tooltipElement) {
                        tooltipElement.remove();
                        tooltipElement = null;
                    }
                });
            }
        }
    }, 500);
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
    
    // Hide status alert when ready (clean interface)
    statusDiv.style.display = 'none';
    statusDiv.className = 'alert alert-success text-center';
    statusMsg.textContent = 'Scanner Ready - Point your device at a QR code';
    scanningIndicator.style.display = 'none';
    
    // Hide checkmark and received label
    const checkmarkIndicator = document.getElementById('checkmark-indicator');
    const receivedLabel = document.getElementById('received-label');
    if (checkmarkIndicator) checkmarkIndicator.style.display = 'none';
    if (receivedLabel) receivedLabel.style.display = 'none';
    
    // Reset processing flag
    isProcessing = false;
    
    // Reset document ID
    currentDocumentId = null;
    
    // Refocus scanner input (after a short delay to ensure everything is reset)
    setTimeout(() => {
        document.getElementById('scanner_input')?.focus();
    }, 100);
}

</script>
@endpush

@endsection
