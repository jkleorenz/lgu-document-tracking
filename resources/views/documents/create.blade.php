@extends('layouts.app')

@section('title', 'Create Document')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
                <li class="breadcrumb-item active">Create Document</li>
            </ol>
        </nav>
        <h2 class="fw-bold"><i class="bi bi-file-plus"></i> Create New Document</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Document Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('documents.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Document Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required 
                                   autofocus>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('document_type') is-invalid @enderror" 
                                    id="document_type" 
                                    name="document_type" 
                                    required>
                                <option value="">Select Document Type</option>
                                @foreach($documentTypes as $type)
                                <option value="{{ $type }}" {{ old('document_type') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                                @endforeach
                            </select>
                            @error('document_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Custom input for "Others" -->
                            <div id="document_type_other_wrapper" class="mt-3 p-3 bg-light rounded border" style="display: none;">
                                <label for="document_type_other" class="form-label fw-bold">
                                    <i class="bi bi-pencil-square"></i> Specify Document Type <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('document_type_other') is-invalid @enderror" 
                                       id="document_type_other" 
                                       name="document_type_other" 
                                       value="{{ old('document_type_other') }}"
                                       placeholder="Enter your custom document type here..."
                                       autocomplete="off">
                                <small class="form-text text-muted">
                                    <i class="bi bi-info-circle"></i> Please enter the specific document type you need.
                                </small>
                                @error('document_type_other')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Forward to Department <span class="text-danger">*</span></label>
                            <select class="form-select @error('department_id') is-invalid @enderror" 
                                    id="department_id" 
                                    name="department_id" 
                                    required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->display_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="5">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Provide additional details about this document</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="is_priority" 
                                       name="is_priority" 
                                       value="1"
                                       {{ old('is_priority') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_priority">
                                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> 
                                    <strong>Mark as PRIORITY</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">Priority documents will be highlighted and require immediate attention</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Document
                            </button>
                            <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>What happens next?</strong></p>
                    <ol class="small">
                        <li>A unique document number will be generated</li>
                        <li>A QR code will be created for tracking</li>
                        <li>The assigned department will be notified</li>
                        <li>You can print the QR code to attach to the physical document</li>
                    </ol>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-lightbulb"></i> <strong>Tip:</strong> Make sure to print and attach the QR code to the physical document for easy tracking!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const documentTypeSelect = document.getElementById('document_type');
    const documentTypeOtherWrapper = document.getElementById('document_type_other_wrapper');
    const documentTypeOtherInput = document.getElementById('document_type_other');
    const form = documentTypeSelect.closest('form');
    
    function toggleOtherInput() {
        if (documentTypeSelect.value === 'Others') {
            documentTypeOtherWrapper.style.display = 'block';
            documentTypeOtherInput.setAttribute('required', 'required');
            documentTypeOtherInput.setAttribute('aria-required', 'true');
            // Focus on the input for better UX
            setTimeout(() => {
                documentTypeOtherInput.focus();
            }, 100);
        } else {
            documentTypeOtherWrapper.style.display = 'none';
            documentTypeOtherInput.removeAttribute('required');
            documentTypeOtherInput.removeAttribute('aria-required');
            documentTypeOtherInput.value = '';
            // Clear any validation errors
            documentTypeOtherInput.classList.remove('is-invalid');
        }
    }
    
    // Validate before form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            if (documentTypeSelect.value === 'Others') {
                if (!documentTypeOtherInput.value || documentTypeOtherInput.value.trim() === '') {
                    e.preventDefault();
                    documentTypeOtherInput.classList.add('is-invalid');
                    documentTypeOtherInput.focus();
                    
                    // Show custom error message
                    let errorDiv = documentTypeOtherInput.nextElementSibling;
                    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        documentTypeOtherInput.parentNode.appendChild(errorDiv);
                    }
                    errorDiv.textContent = 'Please specify the document type.';
                    
                    return false;
                } else {
                    documentTypeOtherInput.classList.remove('is-invalid');
                }
            }
        });
    }
    
    // Check on page load (for validation errors)
    toggleOtherInput();
    
    // Check on change
    documentTypeSelect.addEventListener('change', toggleOtherInput);
    
    // Real-time validation for the custom input
    documentTypeOtherInput.addEventListener('input', function() {
        if (documentTypeSelect.value === 'Others') {
            if (this.value.trim() !== '') {
                this.classList.remove('is-invalid');
            }
        }
    });
});
</script>
@endpush

