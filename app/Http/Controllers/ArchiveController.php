<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    /**
     * Display archived documents
     * Archive page shows only archived-completed documents (status='Completed' with archived_at set)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Archive page shows only archived-completed documents
        // These are documents with status='Completed' AND archived_at IS NOT NULL
        $query = Document::with(['creator', 'department'])
            ->where('status', 'Completed')
            ->whereNotNull('archived_at');

        // Filter based on user role
        if ($user->hasRole('LGU Staff') || $user->hasRole('Department Head')) {
            // LGU Staff and Department Head see:
            // 1. Completed documents they created
            // 2. Completed documents from their department (documents completed by their department)
            $query->where(function($q) use ($user) {
                // Documents created by the user
                $q->where('created_by', $user->id)
                  // OR documents from their department
                  ->orWhere(function($deptQ) use ($user) {
                      if ($user->department_id) {
                          $deptQ->where('department_id', $user->department_id);
                      }
                  });
            });
        }
        // Administrators see all archived-completed documents (no additional filter)

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply date filter
        if ($request->filled('from_date')) {
            $query->whereDate('archived_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('archived_at', '<=', $request->to_date);
        }

        $archivedDocuments = $query->with('statusLogs')->latest('archived_at')->paginate(15);

        return view('archive.index', compact('archivedDocuments'));
    }

    /**
     * Show archived document details
     */
    public function show(Document $document)
    {
        // Ensure document is archived
        if (!$document->isArchived()) {
            abort(404, 'Document is not archived.');
        }

        // Check if user has permission to view this document
        $user = Auth::user();
        
        // LGU Staff and Department Head have identical privileges - can view archived documents (needed for tracking)
        if (!$user->hasRole('Administrator') && !$user->hasRole('LGU Staff') && !$user->hasRole('Department Head')) {
            abort(403, 'Unauthorized access to this document.');
        }

        $document->load(['creator', 'department', 'statusLogs.updatedBy']);

        return view('archive.show', compact('document'));
    }

    /**
     * Restore archived document
     */
    public function restore(Document $document)
    {
        $this->authorize('archive-documents');

        if (!$document->isArchived()) {
            return back()->withErrors(['error' => 'Document is not archived.']);
        }

        // Load department relationship
        $document->load('department.head');
        
        $oldStatus = $document->status;
        
        // Restore the document
        $document->update([
            'status' => 'Under Review',
            'archived_at' => null,
        ]);

        // Log the restoration
        \App\Models\DocumentStatusLog::createLog(
            $document->id,
            Auth::id(),
            $oldStatus,
            'Under Review',
            'Document restored from archive'
        );

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document restored from archive successfully!');
    }

    /**
     * Permanently delete an archived document
     */
    public function destroy(Document $document)
    {
        // Only administrators can permanently delete documents
        $this->authorize('manage-documents');
        
        if (!Auth::user()->hasRole('Administrator')) {
            abort(403, 'Only administrators can permanently delete documents.');
        }

        if (!$document->isArchived()) {
            return back()->withErrors(['error' => 'Only archived documents can be deleted.']);
        }

        $documentNumber = $document->document_number;
        
        // Delete associated files if they exist
        if ($document->qr_code_path && file_exists(public_path($document->qr_code_path))) {
            unlink(public_path($document->qr_code_path));
        }
        
        // Delete the document
        $document->delete();

        return redirect()->route('archive.index')
            ->with('success', "Document {$documentNumber} has been permanently deleted.");
    }
}

