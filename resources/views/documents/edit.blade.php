@extends('layouts.app')

@section('title', 'Edit Document')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
                <li class="breadcrumb-item"><a href="{{ route('documents.show', $document) }}">{{ $document->document_number }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h2 class="fw-bold"><i class="bi bi-pencil-square"></i> Edit Document</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Document Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('documents.update', $document) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Document Number</label>
                            <input type="text" class="form-control" value="{{ $document->document_number }}" disabled>
                            <small class="form-text text-muted">Document number cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Document Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $document->title) }}" 
                                   required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                            @php
                                $currentType = old('document_type', $document->document_type);
                                $isOtherType = !in_array($currentType, $documentTypes);
                                $selectedType = $isOtherType ? 'Others' : $currentType;
                                $otherValue = $isOtherType ? $currentType : old('document_type_other');
                            @endphp
                            
                            <select class="form-select @error('document_type') is-invalid @enderror" 
                                    id="document_type" 
                                    name="document_type" 
                                    required>
                                @foreach($documentTypes as $type)
                                <option value="{{ $type }}" {{ $selectedType == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                                @endforeach
                            </select>
                            @error('document_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Custom input for "Others" -->
                            <div id="document_type_other_wrapper" class="mt-3 p-3 bg-light rounded border" style="display: {{ $isOtherType ? 'block' : 'none' }};">
                                <label for="document_type_other" class="form-label fw-bold">
                                    <i class="bi bi-pencil-square"></i> Specify Document Type <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('document_type_other') is-invalid @enderror" 
                                       id="document_type_other" 
                                       name="document_type_other" 
                                       value="{{ $otherValue }}"
                                       placeholder="Enter your custom document type here..."
                                       autocomplete="off"
                                       {{ $isOtherType ? 'required' : '' }}>
                                <small class="form-text text-muted">
                                    <i class="bi bi-info-circle"></i> Please enter the specific document type you need.
                                </small>
                                @error('document_type_other')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Current Department <span class="text-danger">*</span></label>
                            <select class="form-select @error('department_id') is-invalid @enderror" 
                                    id="department_id" 
                                    name="department_id" 
                                    required>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id', $document->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->display_name }}
                                </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> This is the department currently handling this document
                            </small>
                            @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="forward_to_department" class="form-label">
                                <i class="bi bi-arrow-right-circle"></i> Forward to Department (Optional)
                            </label>
                            <select class="form-select @error('forward_to_department') is-invalid @enderror" 
                                    id="forward_to_department" 
                                    name="forward_to_department">
                                <option value="">-- No Forwarding --</option>
                                @foreach($departments as $department)
                                @if($department->id != $document->department_id)
                                <option value="{{ $department->id }}" {{ old('forward_to_department') == $department->id ? 'selected' : '' }}>
                                    {{ $department->display_name }}
                                </option>
                                @endif
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                <i class="bi bi-arrow-right-circle-fill text-primary"></i> Select a department to forward this document to
                            </small>
                            @error('forward_to_department')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="5">{{ old('description', $document->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Document
                            </button>
                            <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Current Status</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>Current Status:</th>
                            <td>
                                <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Received' ? 'success' : ($document->status == 'Pending' ? 'warning' : 'info')) }}">
                                    {{ $document->status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Priority:</th>
                            <td>
                                @if($document->is_priority)
                                <span class="badge badge-priority">YES</span>
                                @else
                                <span class="badge bg-secondary">NO</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $document->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
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
            // Only clear if switching away from Others
            if (documentTypeSelect.value !== 'Others') {
                documentTypeOtherInput.value = '';
            }
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
    
    // Check on page load
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

