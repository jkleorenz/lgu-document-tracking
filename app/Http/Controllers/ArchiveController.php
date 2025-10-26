<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    /**
     * Display archived documents
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Document::with(['creator', 'department', 'currentHandler'])
            ->archived();

        // Filter based on user role
        if ($user->hasRole('LGU Staff')) {
            $query->where('created_by', $user->id);
        } elseif ($user->hasRole('Department Head')) {
            $query->where('department_id', $user->department_id);
        }

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

        $archivedDocuments = $query->latest('archived_at')->paginate(15);

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
        
        if (!$user->hasRole('Administrator')) {
            if ($user->hasRole('LGU Staff') && $document->created_by !== $user->id) {
                abort(403, 'Unauthorized access to this document.');
            }
            if ($user->hasRole('Department Head') && $document->department_id !== $user->department_id) {
                abort(403, 'Unauthorized access to this document.');
            }
        }

        $document->load(['creator', 'department', 'currentHandler', 'statusLogs.updatedBy']);

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

        $document->update([
            'status' => 'Under Review',
            'archived_at' => null,
        ]);

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

