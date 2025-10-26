<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Department;
use App\Models\DocumentStatusLog;
use App\Models\User;
use App\Services\QRCodeService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    protected $qrCodeService;
    protected $notificationService;

    public function __construct(QRCodeService $qrCodeService, NotificationService $notificationService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of documents
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Document::with(['creator', 'department', 'currentHandler']);

        // Filter based on user role
        if ($user->hasRole('LGU Staff')) {
            $query->where('created_by', $user->id);
        } elseif ($user->hasRole('Department Head')) {
            $query->where('department_id', $user->department_id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('is_priority', true);
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Exclude archived documents unless specifically requested
        if (!$request->filled('show_archived')) {
            $query->active();
        }

        $documents = $query->latest()->paginate(15);
        $departments = Department::active()->get();

        return view('documents.index', compact('documents', 'departments'));
    }

    /**
     * Show the form for creating a new document (Admin only)
     */
    public function create()
    {
        // Only administrators can create documents
        if (!Auth::user()->hasRole('Administrator')) {
            abort(403, 'Only administrators can create documents.');
        }
        
        $departments = Department::active()->get();
        $documentTypes = ['Memorandum', 'Letter', 'Resolution', 'Ordinance', 'Report', 'Request', 'Other'];
        
        return view('documents.create', compact('departments', 'documentTypes'));
    }

    /**
     * Store a newly created document in storage (Admin only)
     */
    public function store(Request $request)
    {
        // Only administrators can create documents
        if (!Auth::user()->hasRole('Administrator')) {
            abort(403, 'Only administrators can create documents.');
        }

        // Validate input
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'document_type' => ['required', 'string'],
            'department_id' => ['required', 'exists:departments,id'],
        ]);

        DB::beginTransaction();
        try {
            // Generate unique document number
            $documentNumber = Document::generateDocumentNumber();

            // Create document (Admin only - no verification needed)
            $document = Document::create([
                'document_number' => $documentNumber,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'document_type' => $validated['document_type'],
                'department_id' => $validated['department_id'],
                'created_by' => Auth::id(),
                'status' => 'Pending',
            ]);

            // Generate QR code
            $qrCodePath = $this->qrCodeService->generateDocumentQRCode(
                $document->document_number,
                $document->id
            );
            $document->update(['qr_code_path' => $qrCodePath]);

            // Create initial status log
            DocumentStatusLog::createLog(
                $document->id,
                Auth::id(),
                null,
                'Pending',
                'Document created by administrator'
            );

            // Notify department users with specific details
            $creator = Auth::user();
            $department = Department::find($document->department_id);
            $this->notificationService->notifyDepartmentUsers(
                $document->department_id,
                'New Document Received - ' . $department->name,
                "New document '{$document->title}' ({$document->document_number}) has been created and assigned to {$department->name} by {$creator->name} (Administrator). Please review and take action.",
                'info',
                $document->id
            );

            DB::commit();

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create document: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified document
     */
    public function show(Document $document)
    {
        // Check if user has permission to view this document
        $user = Auth::user();
        
        if (!$user->hasRole('Administrator')) {
            // LGU Staff can view documents they created
            if ($user->hasRole('LGU Staff') && $document->created_by !== $user->id) {
                abort(403, 'Unauthorized access to this document.');
            }
            
            // Department Head can view documents in their department OR documents they've handled/forwarded
            if ($user->hasRole('Department Head')) {
                $hasHandled = DocumentStatusLog::where('document_id', $document->id)
                    ->where('updated_by', $user->id)
                    ->exists();
                    
                if ($document->department_id !== $user->department_id && !$hasHandled) {
                    abort(403, 'Unauthorized access to this document.');
                }
            }
        }

        $document->load(['creator', 'department', 'currentHandler', 'statusLogs.updatedBy']);
        
        return view('documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified document
     */
    public function edit(Document $document)
    {
        $this->authorize('manage-documents');
        
        // Only creator or admin can edit
        if (!Auth::user()->hasRole('Administrator') && $document->created_by !== Auth::id()) {
            abort(403, 'Unauthorized to edit this document.');
        }

        $departments = Department::active()->get();
        $documentTypes = ['Memorandum', 'Letter', 'Resolution', 'Ordinance', 'Report', 'Request', 'Other'];
        
        return view('documents.edit', compact('document', 'departments', 'documentTypes'));
    }

    /**
     * Update the specified document in storage
     */
    public function update(Request $request, Document $document)
    {
        $this->authorize('manage-documents');

        // Only creator or admin can update
        if (!Auth::user()->hasRole('Administrator') && $document->created_by !== Auth::id()) {
            abort(403, 'Unauthorized to update this document.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'document_type' => ['required', 'string'],
            'department_id' => ['required', 'exists:departments,id'],
            'forward_to_department' => ['nullable', 'exists:departments,id'],
        ]);

        DB::beginTransaction();
        try {
            // Check if document is being forwarded to another department
            if (!empty($validated['forward_to_department']) && 
                $validated['forward_to_department'] != $document->department_id) {
                
                $oldDepartment = $document->department;
                $newDepartmentId = $validated['forward_to_department'];
                $newDepartment = Department::findOrFail($newDepartmentId);
                
                // Update to forwarded department
                $validated['department_id'] = $newDepartmentId;
                $document->update($validated);
                
                // Create status log for forwarding
                DocumentStatusLog::createLog(
                    $document->id,
                    Auth::id(),
                    $document->status,
                    'Forwarded',
                    "Forwarded from {$oldDepartment->name} to {$newDepartment->name}"
                );
                
                // Update status back to indicate forwarding
                $document->update(['status' => 'Under Review']);
                
                // Get current user info
                $forwarder = Auth::user();
                $forwarderRole = $forwarder->roles->first()->name ?? 'User';
                
                // Notify ALL users in the new department with specific details
                $this->notificationService->notifyDepartmentUsers(
                    $newDepartmentId,
                    'Document Received - Forwarded to ' . $newDepartment->name,
                    "Document '{$document->title}' ({$document->document_number}) was forwarded to {$newDepartment->name} by {$forwarder->name} ({$forwarderRole}) from {$oldDepartment->name}. Please review and take action.",
                    'info',
                    $document->id
                );
                
                DB::commit();
                return redirect()->route('documents.show', $document)
                    ->with('success', "Document updated and forwarded to {$newDepartment->name} successfully!");
            }
            
            // Normal update without forwarding
            $document->update($validated);
            
            DB::commit();
            return redirect()->route('documents.show', $document)
                ->with('success', 'Document updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update document: ' . $e->getMessage()]);
        }
    }

    /**
     * Update document status
     */
    public function updateStatus(Request $request, Document $document)
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
            'handler_id' => ['nullable', 'exists:users,id'],
            'forward_to_department' => ['nullable', 'exists:departments,id'],
        ]);

        $oldStatus = $document->status;
        $oldDepartment = $document->department;

        DB::beginTransaction();
        try {
            // Check if document is being forwarded to another department
            if (!empty($validated['forward_to_department']) && 
                $validated['forward_to_department'] != $document->department_id) {
                
                $newDepartmentId = $validated['forward_to_department'];
                $newDepartment = Department::findOrFail($newDepartmentId);
                
                // Update department
                $document->update([
                    'status' => 'Forwarded',
                    'department_id' => $newDepartmentId,
                    'current_handler_id' => null, // Clear handler when forwarding
                ]);
                
                // Log the status change with forwarding info
                $forwardRemarks = "Forwarded from {$oldDepartment->name} to {$newDepartment->name}";
                if (!empty($validated['remarks'])) {
                    $forwardRemarks .= ". " . $validated['remarks'];
                }
                
                DocumentStatusLog::createLog(
                    $document->id,
                    Auth::id(),
                    $oldStatus,
                    'Forwarded',
                    $forwardRemarks
                );
                
                // Get current user info
                $forwarder = Auth::user();
                $forwarderRole = $forwarder->roles->first()->name ?? 'User';
                
                // Notify document creator about forwarding
                $this->notificationService->notifyStatusUpdate(
                    $document,
                    $document->created_by,
                    'Forwarded'
                );
                
                // Notify ALL users in the new department with specific details
                $this->notificationService->notifyDepartmentUsers(
                    $newDepartmentId,
                    'Document Received - Forwarded to ' . $newDepartment->name,
                    "Document '{$document->title}' ({$document->document_number}) was forwarded to {$newDepartment->name} by {$forwarder->name} ({$forwarderRole}) from {$oldDepartment->name}. Please review and take action.",
                    'info',
                    $document->id
                );
                
                DB::commit();
                return back()->with('success', "Document forwarded to {$newDepartment->name} successfully!");
            }
            
            // Normal status update without forwarding
            $updates = [
                'status' => $validated['status'],
                'current_handler_id' => $validated['handler_id'] ?? $document->current_handler_id,
            ];

            // If status is Approved and NOT forwarding, auto-archive (document is finished)
            if ($validated['status'] === 'Approved') {
                $updates['archived_at'] = now();
            }

            $document->update($updates);

            // Log the status change
            DocumentStatusLog::createLog(
                $document->id,
                Auth::id(),
                $oldStatus,
                $validated['status'],
                $validated['remarks'] ?? null
            );

            // Notify document creator
            $this->notificationService->notifyStatusUpdate(
                $document,
                $document->created_by,
                $validated['status']
            );

            // Notify new handler if assigned
            if (isset($validated['handler_id'])) {
                $this->notificationService->notifyDocumentForwarded(
                    $document,
                    $validated['handler_id']
                );
            }

            DB::commit();

            $message = $validated['status'] === 'Approved' 
                ? 'Document approved and archived successfully!' 
                : 'Document status updated successfully!';

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }

    /**
     * Set document as priority
     */
    public function setPriority(Document $document)
    {
        $this->authorize('set-priority');

        $document->update(['is_priority' => !$document->is_priority]);

        if ($document->is_priority) {
            // Notify all relevant users
            $this->notificationService->notifyPriorityDocument(
                $document,
                $document->created_by
            );

            if ($document->current_handler_id) {
                $this->notificationService->notifyPriorityDocument(
                    $document,
                    $document->current_handler_id
                );
            }

            // Notify department users
            $this->notificationService->notifyDepartmentUsers(
                $document->department_id,
                'Priority Document Alert',
                "Document '{$document->title}' has been marked as PRIORITY!",
                'warning',
                $document->id
            );
        }

        $message = $document->is_priority ? 'Document marked as priority.' : 'Priority flag removed.';
        return back()->with('success', $message);
    }

    /**
     * Archive document
     */
    public function archive(Document $document)
    {
        $this->authorize('archive-documents');

        DB::beginTransaction();
        try {
            $document->update([
                'status' => 'Archived',
                'archived_at' => now(),
            ]);

            // Log the archiving
            DocumentStatusLog::createLog(
                $document->id,
                Auth::id(),
                $document->status,
                'Archived',
                'Document archived'
            );

            // Notify document creator
            $this->notificationService->notifyDocumentArchived(
                $document,
                $document->created_by
            );

            DB::commit();

            return back()->with('success', 'Document archived successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to archive document: ' . $e->getMessage()]);
        }
    }

    /**
     * Approve/Verify a document (Admin only)
     */
    public function approve(Document $document)
    {
        if (!Auth::user()->hasRole('Administrator')) {
            abort(403, 'Only administrators can verify documents.');
        }

        if ($document->status !== 'Pending Verification') {
            return back()->withErrors(['error' => 'This document is not pending verification.']);
        }

        DB::beginTransaction();
        try {
            // Update status to Approved and auto-archive (document is finished)
            $document->update([
                'status' => 'Approved',
                'archived_at' => now()
            ]);

            // Log the approval
            DocumentStatusLog::createLog(
                $document->id,
                Auth::id(),
                'Pending Verification',
                'Approved',
                'Document approved and archived by administrator'
            );

            // Notify document creator
            $this->notificationService->notifyStatusUpdate(
                $document,
                $document->created_by,
                'Approved'
            );

            DB::commit();

            return back()->with('success', 'Document approved and archived successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to approve document: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject a document (Admin only)
     */
    public function rejectDocument(Request $request, Document $document)
    {
        if (!Auth::user()->hasRole('Administrator')) {
            abort(403, 'Only administrators can reject documents.');
        }

        if ($document->status !== 'Pending Verification') {
            return back()->withErrors(['error' => 'This document is not pending verification.']);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            // Update status to Rejected
            $document->update(['status' => 'Rejected']);

            // Log the rejection
            DocumentStatusLog::createLog(
                $document->id,
                Auth::id(),
                'Pending Verification',
                'Rejected',
                'Rejected by administrator: ' . $validated['rejection_reason']
            );

            // Notify document creator
            Notification::createNotification(
                $document->created_by,
                'Document Rejected',
                "Your document '{$document->title}' was rejected. Reason: " . $validated['rejection_reason'],
                'danger',
                $document->id
            );

            DB::commit();

            return back()->with('success', 'Document rejected and creator notified.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to reject document: ' . $e->getMessage()]);
        }
    }

    /**
     * Print QR code
     */
    public function printQRCode(Document $document)
    {
        $qrCodePath = $this->qrCodeService->generatePrintableQRCode($document);
        
        return view('documents.print-qr', compact('document', 'qrCodePath'));
    }

    /**
     * Display document timeline
     */
    public function timeline(Document $document)
    {
        // Check if user has access to this document
        $user = Auth::user();
        
        if ($user->hasRole('LGU Staff') && $document->created_by != $user->id) {
            abort(403, 'Unauthorized to view this document timeline.');
        }
        
        if ($user->hasRole('Department Head') && $document->department_id != $user->department_id) {
            abort(403, 'Unauthorized to view this document timeline.');
        }
        
        return view('documents.timeline', compact('document'));
    }

    /**
     * Delete the specified document
     */
    public function destroy(Document $document)
    {
        // Only administrators can delete
        if (!Auth::user()->hasRole('Administrator')) {
            abort(403, 'Unauthorized to delete documents.');
        }

        // Delete QR code file
        if ($document->qr_code_path) {
            $this->qrCodeService->deleteQRCode($document->qr_code_path);
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully!');
    }
}

