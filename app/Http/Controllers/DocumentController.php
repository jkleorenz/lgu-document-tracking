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

        // Exclude archived documents from documents page, EXCEPT when filtering by Completed or "All Status"
        // When filtering by Completed or "All Status", include archived completed documents
        // Archived documents have their own dedicated archive page
        if ($request->filled('status') && $request->status === 'Completed') {
            // When filtering by Completed, allow archived completed documents
            // The role-based filters and status filter will handle the specific logic
            // Just exclude non-completed archived documents
            $query->where(function($q) {
                $q->whereNull('archived_at')
                  ->orWhere(function($subQ) {
                      // Include archived documents that had Completed status
                      $archivedDocIds = DB::table('document_status_logs')
                          ->where('old_status', 'Completed')
                          ->where('new_status', 'Archived')
                          ->distinct()
                          ->pluck('document_id');
                      
                      if ($archivedDocIds->isNotEmpty()) {
                          $subQ->where('status', 'Archived')
                               ->whereIn('id', $archivedDocIds);
                      } else {
                          $subQ->whereRaw('1 = 0');
                      }
                  });
            });
        } elseif (!$request->filled('status')) {
            // "All Status" - allow archived completed documents
            $query->where(function($q) {
                $q->whereNull('archived_at')
                  ->orWhere(function($subQ) {
                      // Include archived documents that had Completed status
                      $archivedDocIds = DB::table('document_status_logs')
                          ->where('old_status', 'Completed')
                          ->where('new_status', 'Archived')
                          ->distinct()
                          ->pluck('document_id');
                      
                      if ($archivedDocIds->isNotEmpty()) {
                          $subQ->where('status', 'Archived')
                               ->whereIn('id', $archivedDocIds);
                      } else {
                          $subQ->whereRaw('1 = 0');
                      }
                  });
            });
        } else {
            // For other status filters, exclude all archived documents
            $query->whereNull('archived_at')
                  ->where('status', '!=', 'Archived');
        }

        // Filter based on user role
        // LGU Staff and Department Head have identical privileges - can see their own documents 
        // AND documents forwarded to their department
        if ($user->hasRole('LGU Staff') || $user->hasRole('Department Head')) {
            $query->where(function($q) use ($user, $request) {
                // Documents created by the user
                $q->where('created_by', $user->id);
                
                // Exclude archived documents from user's created documents, EXCEPT when filtering by Completed
                if (!$request->filled('status') || $request->status !== 'Completed') {
                    $q->whereNull('archived_at')
                      ->where('status', '!=', 'Archived');
                }
                
                // When filtering by Active, exclude completed documents
                if ($request->filled('status') && $request->status === 'Active') {
                    $q->where('status', '!=', 'Completed')
                      ->where(function($subQ) {
                          $subQ->where('status', 'Received')
                               ->orWhere('status', 'Return');
                      });
                }
                // When filtering by Return, only show Return status
                elseif ($request->filled('status') && $request->status === 'Return') {
                    $q->where('status', 'Return');
                }
                // When filtering by Completed, explicitly include Completed status documents
                // Include both active and archived completed documents
                elseif ($request->filled('status') && $request->status === 'Completed') {
                    $q->where(function($completedQ) {
                        $completedQ->where('status', 'Completed')
                            ->orWhere(function($archivedQ) {
                                // Include archived documents that had Completed status
                                $archivedDocIds = DB::table('document_status_logs')
                                    ->where('old_status', 'Completed')
                                    ->where('new_status', 'Archived')
                                    ->distinct()
                                    ->pluck('document_id');
                                
                                if ($archivedDocIds->isNotEmpty()) {
                                    $archivedQ->where('status', 'Archived')
                                             ->whereIn('id', $archivedDocIds);
                                } else {
                                    $archivedQ->whereRaw('1 = 0');
                                }
                            });
                    });
                }
            })->orWhere(function($q) use ($user, $request) {
                // Documents forwarded to their department (even if not created by them)
                // Include Return status documents when filtering by Return or showing all
                if ($user->department_id) {
                    // When filtering by Return, only include Return status documents (non-archived)
                    if ($request->filled('status') && $request->status === 'Return') {
                        $q->where('department_id', $user->department_id)
                          ->where('status', 'Return')
                          ->whereNull('archived_at')
                          ->where('status', '!=', 'Archived');
                    } else {
                        // For other filters or no filter
                        if (!$request->filled('status')) {
                            // When "All Status" is selected, only include: Received, Return, and Completed
                            $q->where('department_id', $user->department_id)
                              ->whereIn('status', ['Received', 'Return', 'Completed']);
                        } elseif ($request->filled('status') && $request->status === 'Completed') {
                            // When filtering by Completed, include Completed status documents in department
                            // Include both active and archived completed documents
                            $q->where('department_id', $user->department_id)
                              ->where(function($completedQ) {
                                  $completedQ->where('status', 'Completed')
                                      ->orWhere(function($archivedQ) {
                                          // Include archived documents that had Completed status
                                          $archivedDocIds = DB::table('document_status_logs')
                                              ->where('old_status', 'Completed')
                                              ->where('new_status', 'Archived')
                                              ->distinct()
                                              ->pluck('document_id');
                                          
                                          if ($archivedDocIds->isNotEmpty()) {
                                              $archivedQ->where('status', 'Archived')
                                                       ->whereIn('id', $archivedDocIds);
                                          } else {
                                              $archivedQ->whereRaw('1 = 0');
                                          }
                                      });
                              });
                        } else {
                            // For other specific status filters, include normal statuses
                            $statuses = ['Forwarded', 'Received', 'Under Review'];
                            $q->where('department_id', $user->department_id)
                              ->whereIn('status', $statuses)
                              ->whereNull('archived_at')
                              ->where('status', '!=', 'Archived');
                        }
                    }
                }
            });
        }
        // Administrators and Mayor can see all documents (no filter applied)

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
            
            // Special handling for "Active" - show only non-archived, non-completed documents
            // Active documents are those still in progress (Received or Return status, not completed)
            if ($status === 'Active') {
                $query->whereNull('archived_at')
                      ->where('status', '!=', 'Completed')
                      ->where('status', '!=', 'Archived')
                      ->where(function($q) {
                          // Include documents with Received or Return status (active documents)
                          $q->where('status', 'Received')
                            ->orWhere('status', 'Return');
                      });
            }
            // Special handling for Completed: include both active and archived completed documents
            // Completed documents may be archived, but we want to show them when filtering by Completed
            elseif ($status === 'Completed') {
                $query->where(function($q) use ($status) {
                    // Documents with current status matching
                    $q->where('status', $status);
                    
                    // OR archived documents that had this status before archiving
                    // Get document IDs from status logs where the old_status matches
                    // and the new_status is "Archived"
                    $archivedDocIds = DB::table('document_status_logs')
                        ->where('old_status', $status)
                        ->where('new_status', 'Archived')
                        ->distinct()
                        ->pluck('document_id');
                    
                    if ($archivedDocIds->isNotEmpty()) {
                        $q->orWhere(function($subQ) use ($archivedDocIds) {
                            $subQ->where('status', 'Archived')
                                 ->whereIn('id', $archivedDocIds);
                        });
                    }
                });
            } elseif ($status === 'Return') {
                // For Return status, only show active (non-archived) documents with Return status
                // Return documents should not be archived
                $query->where('status', 'Return')
                      ->whereNull('archived_at');
            } else {
                // For other statuses (Received, etc.), just filter by current status
                $query->where('status', $status);
            }
        } else {
            // When "All Status" is selected (no status filter), only show: Received, Return, and Completed
            // Include both active and archived completed documents
            $query->where(function($q) {
                // Include Received and Return status documents (active documents)
                $q->where('status', 'Received')
                  ->orWhere('status', 'Return')
                  ->orWhere('status', 'Completed')
                  // Also include archived documents that had Completed status before archiving
                  ->orWhere(function($subQ) {
                      $archivedDocIds = DB::table('document_status_logs')
                          ->where('old_status', 'Completed')
                          ->where('new_status', 'Archived')
                          ->distinct()
                          ->pluck('document_id');
                      
                      if ($archivedDocIds->isNotEmpty()) {
                          $subQ->where('status', 'Archived')
                               ->whereIn('id', $archivedDocIds);
                      } else {
                          // Ensure this condition never matches if no archived completed docs exist
                          $subQ->whereRaw('1 = 0');
                      }
                  });
            });
        }

        // Apply department filter (for Administrators and Mayor)
        if ($request->filled('department') && ($user->hasRole('Administrator') || $user->hasRole('Mayor'))) {
            $query->where('department_id', $request->department);
        }

        // Apply priority filter
        if ($request->filled('priority')) {
            $query->where('is_priority', true);
        }


        // Order by latest and paginate
        // Load status logs for archived documents to check pre-archive status
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

            // Notify department users with specific details
            $priorityText = $document->is_priority ? ' [PRIORITY]' : '';
            $notificationType = $document->is_priority ? 'warning' : 'info';
            $actionText = $document->is_priority ? 'This is a PRIORITY document. Please acknowledge receipt and take immediate action.' : 'Please acknowledge receipt and take action.';
            
            $this->notificationService->notifyDepartmentUsers(
                $document->department_id,
                'New Document Forwarded - ' . $department->name . $priorityText,
                "New document '{$document->title}' ({$document->document_number}) has been forwarded to {$department->name} by {$creator->name} ({$creatorRole}). {$actionText}",
                $notificationType,
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
                    
                    // Notify document creator about status change
                    $this->notificationService->notifyStatusUpdate(
                        $document,
                        $document->created_by,
                        $validated['status']
                    );
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
                
                // Notify document creator about forwarding
                $this->notificationService->notifyStatusUpdate(
                    $document,
                    $document->created_by,
                    'Forwarded'
                );
                
                // Notify ALL users in the new department with specific details
                $statusText = ($validated['status'] !== 'Forwarded' && $validated['status'] !== $oldStatus) 
                    ? "Status changed to {$validated['status']} and " 
                    : "";
                    
                $this->notificationService->notifyDepartmentUsers(
                    $newDepartmentId,
                    'Document Received - Forwarded to ' . $newDepartment->name,
                    "Document '{$document->title}' ({$document->document_number}) was {$statusText}forwarded to {$newDepartment->name} by {$forwarder->name} ({$forwarderRole}) from {$oldDepartment->name}. Please review and take action.",
                    'info',
                    $document->id
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

