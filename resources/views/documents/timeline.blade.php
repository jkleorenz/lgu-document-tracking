@extends('layouts.app')

@section('title', 'Document Timeline - ' . $document->document_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
                <li class="breadcrumb-item"><a href="{{ route('documents.show', $document) }}">{{ $document->document_number }}</a></li>
                <li class="breadcrumb-item active">Timeline</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="fw-bold">
                    <i class="bi bi-clock-history"></i> Document Journey Timeline
                </h2>
                <p class="text-muted mb-0">
                    Tracking history for <strong>{{ $document->document_number }}</strong> - {{ $document->title }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Back to Document
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer"></i> Print Timeline
                </button>
            </div>
        </div>
    </div>
    
    <!-- Document Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6 class="text-muted mb-2">Document Number</h6>
                            <p class="mb-0 fw-bold">{{ $document->document_number }}</p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-2">Current Status</h6>
                            <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Pending' ? 'warning' : 'info') }}">
                                {{ $document->status }}
                            </span>
                            @if($document->is_priority)
                            <span class="badge badge-priority">PRIORITY</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-2">Current Department</h6>
                            <p class="mb-0">{{ $document->department->code }}</p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-2">Current Handler</h6>
                            <p class="mb-0">
                                @if($document->currentHandler)
                                {{ $document->currentHandler->name }}
                                @else
                                <span class="text-muted">Unassigned</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Timeline Component -->
    <div class="row">
        <div class="col-12">
            <x-document-timeline :document="$document" />
        </div>
    </div>
    
    <!-- Export Options (Hidden in Print) -->
    <div class="row mt-4 d-print-none">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="mb-3">
                        <i class="bi bi-download"></i> Export Timeline
                    </h5>
                    <div class="d-flex justify-content-center gap-3">
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="bi bi-file-pdf"></i> Export as PDF
                        </button>
                        <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Return to Document
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .d-print-none {
            display: none !important;
        }
        
        nav, .breadcrumb, .btn {
            display: none !important;
        }
        
        body {
            background: white;
        }
        
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
@endsection

