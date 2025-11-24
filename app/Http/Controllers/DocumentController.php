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
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

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
        $query = Document::with(['creator', 'department']);

        // Documents page shows: Active, Received, Return, and Completed documents
        // Completed documents include both active and archived-completed (status='Completed' with archived_at set)
        // We exclude documents with status='Archived' (manually archived, not completed)
        
        // Base filter: Exclude manually archived documents (status='Archived')
        // But include completed documents even if they have archived_at set
        $query->where(function($q) {
            // Include non-archived documents
            $q->whereNull('archived_at')
              // OR completed documents that are archived (archived-completed)
              ->orWhere(function($completedQ) {
                  $completedQ->where('status', 'Completed')
                             ->whereNotNull('archived_at');
              });
        })->where('status', '!=', 'Archived');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $status = $request->status;
            
            if ($status === 'Active') {
                // Active: documents that are not yet completed, including Forwarded documents
                // Forwarded documents are considered active even if not yet received
                // For LGU Staff/Dept Head: Documents they created OR documents forwarded to their department
                // For Admin: All documents not yet completed (including Forwarded)
                $query->whereNull('archived_at')
                      ->where('status', '!=', 'Completed')
                      ->where('status', '!=', 'Archived')
                      ->whereIn('status', ['Forwarded', 'Received', 'Under Review', 'Pending', 'Return']);
                
                // For LGU Staff and Department Head: Active = documents they created OR documents forwarded to their department
                if ($user->hasRole('LGU Staff') || $user->hasRole('Department Head')) {
                    $query->where(function($q) use ($user) {
                        // Documents created by the user
                        $q->where('created_by', $user->id)
                          // OR documents forwarded to their department (including Forwarded status)
                          ->orWhere(function($deptQ) use ($user) {
                              if ($user->department_id) {
                                  $deptQ->where('department_id', $user->department_id);
                              }
                          });
                    });
                }
                // Administrators see all non-completed documents including Forwarded (no additional filter)
            } elseif ($status === 'Completed') {
                // Completed: include both active and archived-completed documents
                $query->where(function($q) {
                    $q->where('status', 'Completed');
                });
            } elseif ($status === 'Return') {
                // Return: only active (non-archived) Return documents
                $query->where('status', 'Return')
                      ->whereNull('archived_at');
            } elseif ($status === 'Received') {
                // Received: only active (non-archived) Received documents
                $query->where('status', 'Received')
                      ->whereNull('archived_at');
            } else {
                // For other statuses, filter by status and exclude archived
                $query->where('status', $status)
                      ->whereNull('archived_at');
            }
        } else {
            // "All Status": Show all non-archived documents (Active, Received, Return, Completed)
            // Exclude only manually archived documents (status='Archived')
            $query->where('status', '!=', 'Archived');
        }

        // Filter based on user role (applied after status filter for non-Active statuses)
        // For Active filter, role filtering is already applied above
        if (($user->hasRole('LGU Staff') || $user->hasRole('Department Head')) && (!$request->filled('status') || $request->status !== 'Active')) {
            // LGU Staff and Department Head can see:
            // 1. Documents they created
            // 2. Documents forwarded to their department
            // (This applies to Received, Return, Completed, and "All Status" filters)
            $query->where(function($q) use ($user) {
                // Documents created by the user
                $q->where('created_by', $user->id)
                  // OR documents forwarded to their department
                  ->orWhere(function($deptQ) use ($user) {
                      if ($user->department_id) {
                          $deptQ->where('department_id', $user->department_id);
                      }
                  });
            });
        }
        // Administrators and Mayor can see all documents (no additional filter)

        // Apply department filter (for Administrators and Mayor)
        if ($request->filled('department') && ($user->hasRole('Administrator') || $user->hasRole('Mayor'))) {
            $query->where('department_id', $request->department);
        }

        // Apply priority filter
        if ($request->filled('priority')) {
            $query->where('is_priority', true);
        }

        // Order by latest and paginate
        $documents = $query->with('statusLogs')->latest()->paginate(15)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();

        return view('documents.index', compact('documents', 'departments'));
    }

    /**
     * Show the form for creating a new document (Admin, Mayor, LGU Staff, Department Head)
     */
    public function create()
    {
        // Administrators, Mayor, LGU Staff, and Department Heads can create documents
        if (!Auth::user()->hasAnyRole(['Administrator', 'Mayor', 'LGU Staff', 'Department Head'])) {
            abort(403, 'Only administrators, mayor, LGU staff, and department heads can create documents.');
        }
        
        $departments = Department::active()->orderBy('name')->get();
        $documentTypes = [
            'Certification',
            'Disbursement Voucher',
            'Leave Form',
            'Obligation Request',
            'Program of the Work',
            'SEF',
            'Service Record',
            'Others'
        ];
        
        return view('documents.create', compact('departments', 'documentTypes'));
    }

    /**
     * Store a newly created document in storage (Admin, Mayor, LGU Staff, Department Head)
     */
    public function store(Request $request)
    {
        // Administrators, Mayor, LGU Staff, and Department Heads can create documents
        if (!Auth::user()->hasAnyRole(['Administrator', 'Mayor', 'LGU Staff', 'Department Head'])) {
            abort(403, 'Only administrators, mayor, LGU staff, and department heads can create documents.');
        }

        // Validate input
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'document_type' => ['required', 'string'],
            'document_type_other' => ['nullable', 'string', 'max:255', 'required_if:document_type,Others'],
            'department_id' => ['required', 'exists:departments,id'],
            'is_priority' => ['nullable', 'boolean'],
        ]);

        // Use custom type if "Others" is selected
        $documentType = $validated['document_type'] === 'Others' 
            ? $validated['document_type_other'] 
            : $validated['document_type'];

        DB::beginTransaction();
        try {
            // Generate unique document number
            $documentNumber = Document::generateDocumentNumber();

            // Get department and creator info before creating document
            $department = Department::find($validated['department_id']);
            $creator = Auth::user();
            $creatorRole = $creator->roles->first()->name ?? 'User';

            // Create document (forwarded to department)
            $document = Document::create([
                'document_number' => $documentNumber,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'document_type' => $documentType,
                'department_id' => $validated['department_id'],
                'created_by' => Auth::id(),
                'status' => 'Forwarded',
                'is_priority' => $request->has('is_priority') ? true : false,
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
                'Forwarded',
                "Document forwarded to {$department->name}"
            );

            // EVENT 1: Document Forwarded
            // Get creator's department for old department (if creator has a department)
            $oldDepartment = $creator->department ?? null;
            if (!$oldDepartment) {
                // If creator has no department, create a dummy department object for notification
                $oldDepartment = (object)['id' => null, 'name' => 'System'];
            }
            
            // Notify using new notification system
            // Note: Creator is not notified when they forward themselves (handled in onDocumentForwarded)
            $this->notificationService->onDocumentForwarded(
                $document,
                $oldDepartment,
                $department,
                $creator
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
        
        // LGU Staff, Department Head, Administrators, and Mayor can view all documents (needed for QR scanning and tracking)
        if (!$user->hasRole('Administrator') && !$user->hasRole('Mayor') && !$user->hasRole('LGU Staff') && !$user->hasRole('Department Head')) {
            abort(403, 'Unauthorized access to this document.');
        }

        $document->load(['creator', 'department', 'currentHandler', 'statusLogs.updatedBy']);
        
        // Calculate last location (previous department before current)
        $lastLocation = 'N/A';
        if ($document->department_id) {
            // Get status logs ordered by date (newest first)
            $statusLogs = DocumentStatusLog::where('document_id', $document->id)
                ->with('updatedBy.department')
                ->orderBy('action_date', 'desc')
                ->get();
            
            // Find the first department in logs that's different from current department
            foreach ($statusLogs as $log) {
                if ($log->updatedBy && $log->updatedBy->department_id && 
                    $log->updatedBy->department_id != $document->department_id) {
                    if (!$log->updatedBy->relationLoaded('department')) {
                        $log->updatedBy->load('department');
                    }
                    $lastLocation = $log->updatedBy->department ? $log->updatedBy->department->name : 'N/A';
                    break;
                }
            }
            
            // If no previous department found, check creator's department
            if ($lastLocation === 'N/A' && $document->creator) {
                if (!$document->creator->relationLoaded('department')) {
                    $document->creator->load('department');
                }
                if ($document->creator->department_id && 
                    $document->creator->department_id != $document->department_id) {
                    $lastLocation = $document->creator->department ? $document->creator->department->name : 'N/A';
                }
            }
        }
        
        return view('documents.show', compact('document', 'lastLocation'));
    }

    /**
     * Show the form for editing the specified document
     */
    public function edit(Document $document)
    {
        $this->authorize('manage-documents');
        
        // Only creator, admin, or mayor can edit
        if (!Auth::user()->hasAnyRole(['Administrator', 'Mayor']) && $document->created_by !== Auth::id()) {
            abort(403, 'Unauthorized to edit this document.');
        }

        $departments = Department::active()->orderBy('name')->get();
        $documentTypes = [
            'Certification',
            'Disbursement Voucher',
            'Leave Form',
            'Obligation Request',
            'Program of the Work',
            'SEF',
            'Service Record',
            'Others'
        ];
        
        return view('documents.edit', compact('document', 'departments', 'documentTypes'));
    }

    /**
     * Update the specified document in storage
     */
    public function update(Request $request, Document $document)
    {
        $this->authorize('manage-documents');

        // Only creator, admin, or mayor can update
        if (!Auth::user()->hasAnyRole(['Administrator', 'Mayor']) && $document->created_by !== Auth::id()) {
            abort(403, 'Unauthorized to update this document.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'document_type' => ['required', 'string'],
            'document_type_other' => ['nullable', 'string', 'max:255', 'required_if:document_type,Others'],
            'department_id' => ['required', 'exists:departments,id'],
            'forward_to_department' => ['nullable', 'exists:departments,id'],
        ]);

        // Use custom type if "Others" is selected
        $documentType = $validated['document_type'] === 'Others' 
            ? $validated['document_type_other'] 
            : $validated['document_type'];

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
                $validated['document_type'] = $documentType;
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
                
                // EVENT 1: Document Forwarded
                // Refresh document to get updated relationships
                $document->refresh();
                $this->notificationService->onDocumentForwarded(
                    $document,
                    $oldDepartment,
                    $newDepartment,
                    $forwarder
                );
                
                DB::commit();
                return redirect()->route('documents.show', $document)
                    ->with('success', "Document updated and forwarded to {$newDepartment->name} successfully!");
            }
            
            // Normal update without forwarding
            $validated['document_type'] = $documentType;
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
                
                // Get current user info
                $forwarder = Auth::user();
                $forwarderRole = $forwarder->roles->first()->name ?? 'User';
                
                // Check if both status change and forwarding are happening
                $isStatusChange = $validated['status'] !== 'Forwarded' && $validated['status'] !== $oldStatus;
                
                if ($isStatusChange) {
                    // Create a single combined log entry for both actions
                    $combinedRemarks = "Approved and forwarded from {$oldDepartment->name} to {$newDepartment->name}";
                    if (!empty($validated['remarks'])) {
                        $combinedRemarks .= ". " . $validated['remarks'];
                    }
                    
                    DocumentStatusLog::createLog(
                        $document->id,
                        Auth::id(),
                        $oldStatus,
                        $validated['status'], // Status will be "Approved"
                        $combinedRemarks
                    );
                    
                    // Do NOT notify document creator about forwarding
                    // Only notify the forwarded department
                } else {
                    // Only forwarding, no status change
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
                }
                
                // Update department and status to Forwarded
                $document->update([
                    'status' => 'Forwarded',
                    'department_id' => $newDepartmentId,
                ]);
                
                // EVENT 1: Document Forwarded
                // Refresh document to get updated relationships
                $document->refresh();
                $this->notificationService->onDocumentForwarded(
                    $document,
                    $oldDepartment,
                    $newDepartment,
                    $forwarder
                );
                
                DB::commit();
                
                $message = ($validated['status'] !== 'Forwarded' && $validated['status'] !== $oldStatus)
                    ? "Document status changed to {$validated['status']} and forwarded to {$newDepartment->name} successfully!"
                    : "Document forwarded to {$newDepartment->name} successfully!";
                    
                return back()->with('success', $message);
            }
            
            // Normal status update without forwarding
            $updates = [
                'status' => $validated['status'],
            ];

            // If status is Approved and NOT forwarding, auto-archive (document is finished)
            if ($validated['status'] === 'Approved') {
                $updates['archived_at'] = now();
            }
            
            // If status is Completed, auto-archive (document is finished)
            // Keep status as 'Completed' but set archived_at to mark it as archived-completed
            // This allows it to appear in both Documents page and Archive page
            if ($validated['status'] === 'Completed') {
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

            // EVENT 4: Document Completed / Archived-Completed
            if ($validated['status'] === 'Completed') {
                $document->refresh();
                $this->notificationService->onDocumentCompleted(
                    $document,
                    Auth::user()
                );
            }

            DB::commit();

            $message = $validated['status'] === 'Approved' 
                ? 'Document approved and archived successfully!' 
                : ($validated['status'] === 'Completed'
                    ? 'Document completed and archived successfully!'
                    : 'Document status updated successfully!');

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
            // Save the old status BEFORE updating
            $oldStatus = $document->status;
            
            $document->update([
                'status' => 'Archived',
                'archived_at' => now(),
            ]);

            // Log the archiving with the correct old status
            DocumentStatusLog::createLog(
                $document->id,
                Auth::id(),
                $oldStatus,
                'Archived',
                'Document archived'
            );

            // EVENT 5: Document Archived-Not Completed
            // Refresh document to get updated relationships
            $document->refresh();
            $this->notificationService->onDocumentArchivedNotCompleted(
                $document,
                Auth::user()
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

            // EVENT 4: Document Completed / Archived-Completed
            // Approved status with archived_at = completed/archived-completed
            $document->refresh();
            $this->notificationService->onDocumentCompleted(
                $document,
                Auth::user()
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
            $this->notificationService->notifyCreator(
                $document,
                'Document Rejected',
                "Your document '{$document->title}' was rejected. Reason: " . $validated['rejection_reason'],
                'danger'
            );
            
            // Also notify administrators about rejection
            $this->notificationService->notifyAdministrators(
                'Document Rejected',
                "Document '{$document->title}' ({$document->document_number}) was rejected. Reason: " . $validated['rejection_reason'],
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
     * Generate document report (PDF or DOCX)
     */
    public function generateReport(Document $document, Request $request)
    {
        $format = $request->get('format', 'pdf'); // pdf or docx
        
        // Load relationships
        $document->load(['creator', 'department', 'statusLogs.updatedBy.department']);
        
        if ($format === 'pdf') {
            return $this->generatePDFReport($document);
        } elseif ($format === 'docx') {
            return $this->generateDOCXReport($document);
        }
        
        abort(400, 'Invalid format. Please use pdf or docx.');
    }

    /**
     * Generate PDF report
     */
    private function generatePDFReport(Document $document)
    {
        $pdf = Pdf::loadView('documents.report-pdf', compact('document'));
        $pdf->setPaper('A4', 'portrait');
        $filename = 'Document_Report_' . $document->document_number . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Generate DOCX report
     */
    private function generateDOCXReport(Document $document)
    {
        $phpWord = new PhpWord();
        
        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('LGU Document Tracking System');
        $properties->setTitle('Document Report - ' . $document->document_number);
        $properties->setSubject('Document Report');
        
        // Add section
        $section = $phpWord->addSection([
            'marginTop' => 1440,    // 1 inch = 1440 twips
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);
        
        // Header
        $header = $section->addHeader();
        $header->addText('Document Report', ['bold' => true, 'size' => 16]);
        $header->addText('Generated on: ' . now()->format('F d, Y h:i A'), ['size' => 10, 'color' => '666666']);
        
        // Title
        $section->addTitle('Document Information', 1);
        
        // Document Information
        $infoTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        
        $infoRows = [
            ['Document Title', $document->title],
            ['Document #', $document->document_number],
            ['Current Location Department', $document->department ? $document->department->name : 'N/A'],
            ['Document Type', $document->document_type],
            ['Created By', $document->creator ? $document->creator->name : 'Unknown'],
            ['Status', $document->status],
            ['Created Date', $document->created_at->format('F d, Y h:i A')],
        ];
        
        foreach ($infoRows as $row) {
            $infoTable->addRow();
            $infoTable->addCell(3000)->addText($row[0], ['bold' => true]);
            $infoTable->addCell(5000)->addText($row[1]);
        }
        
        // Document History
        $section->addTitle('Document History', 1);
        
        if ($document->statusLogs->count() > 0) {
            $historyTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
            
            // Header row
            $historyTable->addRow();
            $historyTable->addCell(2000)->addText('Date & Time', ['bold' => true]);
            $historyTable->addCell(1500)->addText('Old Status', ['bold' => true]);
            $historyTable->addCell(1500)->addText('New Status', ['bold' => true]);
            $historyTable->addCell(2000)->addText('Updated By', ['bold' => true]);
            $historyTable->addCell(3000)->addText('Remarks', ['bold' => true]);
            
            // Data rows
            foreach ($document->statusLogs as $log) {
                $historyTable->addRow();
                $historyTable->addCell(2000)->addText($log->created_at->format('M d, Y h:i A'));
                $historyTable->addCell(1500)->addText($log->old_status ?? 'N/A');
                $historyTable->addCell(1500)->addText($log->new_status);
                $historyTable->addCell(2000)->addText($log->updatedBy ? $log->updatedBy->name : 'System');
                $historyTable->addCell(3000)->addText($log->remarks ?? '-');
            }
        } else {
            $section->addText('No history available.', ['italic' => true]);
        }
        
        // Save to temporary file
        $filename = 'Document_Report_' . $document->document_number . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
        
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Display document timeline
     */
    public function timeline(Document $document)
    {
        // Check if user has access to this document
        $user = Auth::user();
        
        // LGU Staff, Department Head, Administrators, and Mayor can view all document timelines (needed for tracking)
        if (!$user->hasRole('Administrator') && !$user->hasRole('Mayor') && !$user->hasRole('LGU Staff') && !$user->hasRole('Department Head')) {
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

