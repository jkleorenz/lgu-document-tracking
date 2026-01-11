<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArchiveController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display archived documents
     * Archive page shows all archived documents (both 'Completed' and 'Archived' status with archived_at set)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Archive page shows all archived documents
        // These are documents with (status='Completed' OR status='Archived') AND archived_at IS NOT NULL
        $query = Document::with(['creator', 'department'])
            ->whereIn('status', ['Completed', 'Archived'])
            ->whereNotNull('archived_at');

        // Filter based on user role
        if ($user->hasRole('LGU Staff') || $user->hasRole('Department Head')) {
            // LGU Staff and Department Head see:
            // 1. Archived documents they created (regardless of current department_id)
            // 2. Archived documents from their department (documents archived by their department)
            // This ensures that when a document is forwarded and archived by the receiving department,
            // it appears in both the creator's archive list and the receiving department's archive list
            $query->where(function($q) use ($user) {
                // Documents created by the user (creator can always see their archived documents)
                $q->where('created_by', $user->id)
                  // OR documents from their department (receiving department can see documents they archived)
                  ->orWhere(function($deptQ) use ($user) {
                      if ($user->department_id) {
                          $deptQ->where('department_id', $user->department_id);
                      }
                  });
            });
        }
        // Administrators see all archived documents (no additional filter)

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
        
        // Administrators can view all archived documents
        if ($user->hasRole('Administrator')) {
            $document->load(['creator', 'department', 'statusLogs.updatedBy']);
            return view('archive.show', compact('document'));
        }
        
        // LGU Staff and Department Head can view archived documents if:
        // 1. They created the document, OR
        // 2. The document is in their department
        if ($user->hasRole('LGU Staff') || $user->hasRole('Department Head')) {
            $canView = false;
            
            // Check if user created the document
            if ($document->created_by === $user->id) {
                $canView = true;
            }
            
            // Check if document is in user's department
            if (!$canView && $user->department_id && $document->department_id === $user->department_id) {
                $canView = true;
            }
            
            if (!$canView) {
                abort(403, 'Unauthorized access to this document.');
            }
            
            $document->load(['creator', 'department', 'statusLogs.updatedBy']);
            return view('archive.show', compact('document'));
        }
        
        // Other roles cannot view archived documents
        abort(403, 'Unauthorized access to this document.');
    }

    /**
     * Retrieve archived document
     */
    public function restore(Document $document)
    {
        $this->authorize('archive-documents');

        if (!$document->isArchived()) {
            return back()->withErrors(['error' => 'Document is not archived.']);
        }

        DB::beginTransaction();
        try {
            // Load department relationship and creator
            $document->load(['department.head', 'creator']);
            
            $oldStatus = $document->status;
            $retrievedBy = Auth::user();
            
            // Retrieve the document to 'Retrieved' status so it can be received by scanning department
            // This ensures retrieved documents are fully receivable and processed normally
            $document->update([
                'status' => 'Retrieved',
                'archived_at' => null,
            ]);

            // Log the retrieval
            \App\Models\DocumentStatusLog::createLog(
                $document->id,
                Auth::id(),
                $oldStatus,
                'Retrieved',
                'Document retrieved from archive'
            );

            // EVENT 6: Document Retrieved from Archive
            // Refresh document to get updated relationships
            $document->refresh();
            $this->notificationService->onDocumentRetrieved(
                $document,
                $retrievedBy
            );

            DB::commit();

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document retrieved from archive successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to retrieve document: ' . $e->getMessage()]);
        }
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

