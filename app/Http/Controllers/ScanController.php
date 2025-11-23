<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentStatusLog;
use App\Models\Notification;
use App\Models\Department;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScanController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Show the QR code scanner page
     */
    public function index(Request $request)
    {
        // If coming from QR code scan with document number
        if ($request->filled('document')) {
            $document = Document::where('document_number', $request->document)
                ->with(['creator', 'department', 'statusLogs.updatedBy'])
                ->first();

            if ($document) {
                return view('scan.index', compact('document'));
            } else {
                return view('scan.index')->withErrors(['error' => 'Document not found.']);
            }
        }

        return view('scan.index');
    }

    /**
     * Process QR code scan
     * Automatically receives the document and logs tracking information
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'document_number' => ['required', 'string'],
        ]);

        // Find document by document number
        $document = Document::where('document_number', $validated['document_number'])
            ->with(['creator', 'department', 'statusLogs.updatedBy'])
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.',
            ], 404);
        }

        // Check if user has permission to view this document
        $user = Auth::user();
        
        // Administrators, Mayor, LGU Staff, and Department Head can scan documents
        if (!$user->hasRole('Administrator') && !$user->hasRole('Mayor') && !$user->hasRole('LGU Staff') && !$user->hasRole('Department Head')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to scan this document.',
            ], 403);
        }

        $oldStatus = $document->status;
        $oldDepartment = $document->department;
        $oldDepartmentId = $document->department_id;
        $statusChanged = false;
        $departmentChanged = false;

        DB::beginTransaction();
        try {
            // Always update location to scanner's department if scanner has a department
            if ($user->department_id && !$document->isArchived()) {
                // Update department to scanner's department if different
                if ($document->department_id != $user->department_id) {
                    $document->department_id = $user->department_id;
                    $departmentChanged = true;
                }
            }
            
            // Auto-receive document if it's in Forwarded, Pending, or Return status
            // IMPORTANT: Scanning should NEVER set status to 'Return' - only the Return action button does that
            // If document is in 'Return' status and gets scanned, change it to 'Received'
            if (in_array($document->status, ['Forwarded', 'Pending', 'Return']) && !$document->isArchived()) {
                // Update status to Received (never to Return)
                $document->status = 'Received';
                $statusChanged = true;
            }
            
            // Ensure status is never 'Return' after scanning (safety check)
            if ($document->status === 'Return' && !$document->isArchived()) {
                $document->status = 'Received';
                $statusChanged = true;
            }
            
            $document->save();
            
            // Get scanner department for logging and notifications
            $scannerDepartment = $user->department ? $user->department->name : 'Unknown Department';
            $scannerName = $user->name;
            $scannerRole = $user->roles->first()->name ?? 'User';
            
            // Log the scan with location update
            if ($statusChanged) {
                // Check if a duplicate log was just created (within last 2 seconds)
                $recentLog = DocumentStatusLog::where('document_id', $document->id)
                    ->where('updated_by', $user->id)
                    ->where('old_status', $oldStatus)
                    ->where('new_status', 'Received')
                    ->where('created_at', '>=', now()->subSeconds(2))
                    ->first();
                
                // Only create log if no duplicate exists
                if (!$recentLog) {
                    DocumentStatusLog::createLog(
                        $document->id,
                        $user->id,
                        $oldStatus,
                        'Received',
                        null
                    );
                }
                
                // Notify document creator about the receipt
                if ($document->created_by != $user->id) {
                    Notification::createNotification(
                        $document->created_by,
                        'Document Received via QR Scan',
                        "Document '{$document->title}' ({$document->document_number}) was received at {$scannerDepartment} via QR scan by {$scannerName}.",
                        'success',
                        $document->id
                    );
                }
                
                // Notify old department head if document was moved to a different department
                if ($departmentChanged && $oldDepartment && $oldDepartment->head_id && $oldDepartment->head_id != $user->id) {
                    Notification::createNotification(
                        $oldDepartment->head_id,
                        'Document Received at Different Department',
                        "Document '{$document->title}' ({$document->document_number}) was received at {$scannerDepartment} via QR scan by {$scannerName}.",
                        'info',
                        $document->id
                    );
                }
            } else {
                // Just location update or tracking scan - log without remarks
                // Check if a duplicate log was just created (within last 2 seconds)
                $recentLog = DocumentStatusLog::where('document_id', $document->id)
                    ->where('updated_by', $user->id)
                    ->where('old_status', $document->status)
                    ->where('new_status', $document->status)
                    ->where('created_at', '>=', now()->subSeconds(2))
                    ->first();
                
                // Only create log if no duplicate exists
                if (!$recentLog) {
                    DocumentStatusLog::createLog(
                        $document->id,
                        $user->id,
                        $document->status,
                        $document->status,
                        null
                    );
                }
            }

            DB::commit();

            // Reload document to get updated relationships
            $document->refresh();
            $document->load(['creator', 'department']);

            // Get last location (previous department before current)
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

            return response()->json([
                'success' => true,
                'message' => $statusChanged 
                    ? 'Document received successfully via QR scan!' 
                    : ($departmentChanged 
                        ? 'Document location updated successfully!' 
                        : 'Document scanned successfully. Tracking information logged.'),
                'document' => [
                    'id' => $document->id,
                    'document_number' => $document->document_number,
                    'title' => $document->title,
                    'description' => $document->description,
                    'document_type' => $document->document_type,
                    'status' => $document->status,
                    'is_priority' => $document->is_priority,
                    'department' => $document->department ? $document->department->name : 'N/A',
                    'last_location' => $lastLocation,
                    'created_by' => $document->creator ? $document->creator->name : 'Unknown',
                    'created_at' => $document->created_at->format('M d, Y h:i A'),
                ],
                'scanner' => [
                    'name' => $scannerName,
                    'department' => $scannerDepartment,
                    'role' => $scannerRole,
                ],
                'status_changed' => $statusChanged,
                'department_changed' => $departmentChanged,
                'redirect_url' => route('documents.show', $document),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process scan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Quick status update via scan
     */
    public function quickUpdate(Request $request)
    {
        $validated = $request->validate([
            'document_id' => ['required', 'exists:documents,id'],
            'status' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        $document = Document::findOrFail($validated['document_id']);
        $oldStatus = $document->status;

        DB::beginTransaction();
        try {
            // Update document status
            $document->update([
                'status' => $validated['status'],
            ]);

            // Log the status change
            DocumentStatusLog::createLog(
                $document->id,
                Auth::id(),
                $oldStatus,
                $validated['status'],
                $validated['remarks'] ?? 'Updated via QR scan'
            );

            // Notify document creator
            $this->notificationService->notifyStatusUpdate(
                $document,
                $document->created_by,
                $validated['status']
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document status updated successfully!',
                'document' => [
                    'status' => $document->status,
                    'updated_at' => $document->updated_at->format('M d, Y h:i A'),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document status.',
            ], 500);
        }
    }

    /**
     * Mark document as completed, approve it, and automatically archive it
     */
    public function complete(Request $request)
    {
        $validated = $request->validate([
            'document_id' => ['required', 'exists:documents,id'],
        ]);

        $document = Document::findOrFail($validated['document_id']);
        $user = Auth::user();
        $oldStatus = $document->status;

        // Check if document is archived
        if ($document->isArchived()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot complete an archived document.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Update document status to Completed and archive it
            $document->status = 'Completed';
            $document->archived_at = now();
            $document->save();

            // Log the status change
            DocumentStatusLog::createLog(
                $document->id,
                $user->id,
                $oldStatus,
                'Completed',
                'Document completed and approved'
            );

            // Notify document creator
            if ($document->created_by != $user->id) {
                Notification::createNotification(
                    $document->created_by,
                    'Document Completed and Approved',
                    "Document '{$document->title}' ({$document->document_number}) has been marked as complete and approved by {$user->name}.",
                    'success',
                    $document->id
                );
            }

            DB::commit();

            // Reload document with relationships for response
            $document->refresh();
            $document->load(['creator', 'department']);

            return response()->json([
                'success' => true,
                'message' => 'Document marked as complete and approved successfully!',
                'document' => [
                    'id' => $document->id,
                    'document_number' => $document->document_number,
                    'title' => $document->title,
                    'status' => $document->status,
                    'department' => $document->department ? $document->department->name : 'N/A',
                    'created_by' => $document->creator ? $document->creator->name : 'Unknown',
                    'updated_at' => $document->updated_at->format('M d, Y h:i A'),
                ],
                'redirect_url' => route('documents.show', $document),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return document with remarks
     */
    public function returnDocument(Request $request)
    {
        $validated = $request->validate([
            'document_id' => ['required', 'exists:documents,id'],
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        $document = Document::with(['creator.department', 'department'])->findOrFail($validated['document_id']);
        $user = Auth::user();
        
        // Load user's department relationship
        if (!$user->relationLoaded('department')) {
            $user->load('department');
        }
        
        $oldStatus = $document->status;
        $oldDepartment = $document->department;
        $oldDepartmentId = $document->department_id;
        $oldDepartmentName = $oldDepartment ? $oldDepartment->name : 'Unknown Department';
        
        // Ensure user has a valid department
        if (!$user->department_id) {
            return response()->json([
                'success' => false,
                'message' => 'You must be assigned to a department to return documents.',
            ], 400);
        }

        // Check if document is archived
        if ($document->isArchived()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot return an archived document.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Find the last department that handled this document (before current department)
            // Look through status logs in reverse order to find the previous department
            $lastDepartmentId = null;
            $lastDepartmentName = 'Unknown Department';
            $lastHandlerId = null;
            
            // Get status logs ordered by date (newest first)
            $statusLogs = \App\Models\DocumentStatusLog::where('document_id', $document->id)
                ->with('updatedBy.department')
                ->orderBy('action_date', 'desc')
                ->get();
            
            // Find the first department in logs that's different from current department
            foreach ($statusLogs as $log) {
                if ($log->updatedBy && $log->updatedBy->department_id && 
                    $log->updatedBy->department_id != $oldDepartmentId) {
                    $lastDepartmentId = $log->updatedBy->department_id;
                    // Ensure department relationship is loaded
                    if (!$log->updatedBy->relationLoaded('department')) {
                        $log->updatedBy->load('department');
                    }
                    $lastDepartmentName = $log->updatedBy->department ? $log->updatedBy->department->name : 'Unknown Department';
                    $lastHandlerId = $log->updatedBy->id;
                    break;
                }
            }
            
            // If no previous department found in logs, use creator's department as fallback
            if (!$lastDepartmentId) {
                $creator = $document->creator;
                if ($creator && $creator->department_id && $creator->department_id != $oldDepartmentId) {
                    $lastDepartmentId = $creator->department_id;
                    if (!$creator->relationLoaded('department')) {
                        $creator->load('department');
                    }
                    $lastDepartmentName = $creator->department ? $creator->department->name : 'Unknown Department';
                    $lastHandlerId = $creator->id;
                } else {
                    // If still no department, use creator's department even if same (fallback)
                    if ($creator && $creator->department_id) {
                        $lastDepartmentId = $creator->department_id;
                        if (!$creator->relationLoaded('department')) {
                            $creator->load('department');
                        }
                        $lastDepartmentName = $creator->department ? $creator->department->name : 'Unknown Department';
                        $lastHandlerId = $creator->id;
                    } else {
                        // Last resort: keep current department
                        $lastDepartmentId = $oldDepartmentId;
                        $lastDepartmentName = $oldDepartmentName;
                    }
                }
            }

            // Ensure the department exists before returning
            if (!$lastDepartmentId) {
                throw new \Exception('Unable to determine the department to return the document to.');
            }
            
            // Verify the department exists
            $targetDepartment = Department::find($lastDepartmentId);
            if (!$targetDepartment) {
                throw new \Exception("Target department (ID: {$lastDepartmentId}) not found.");
            }
            
            // Return document to the last department that handled it
            $document->department_id = $lastDepartmentId;
            
            // Update status to Return
            $document->status = 'Return';
            $document->save();
            
            // Refresh document to get updated relationships
            $document->refresh();
            $document->load('department');

            // Get current user's department name for the status log and notifications
            $currentUserDepartment = $user->department ? $user->department->name : 'Unknown Department';
            
            // Format remarks to include "Returned by [Department Name]" in Document History
            $remarks = "Returned by {$currentUserDepartment}";
            if (!empty($validated['remarks'])) {
                $remarks .= " - " . $validated['remarks'];
            }

            // Log the return with formatted remarks in Document History
            DocumentStatusLog::createLog(
                $document->id,
                $user->id,
                $oldStatus,
                'Return',
                $remarks
            );

            // Notify all users in the department being returned to
            try {
                $this->notificationService->notifyDepartmentUsers(
                    $lastDepartmentId,
                    'Document Returned to ' . $lastDepartmentName,
                    "Document '{$document->title}' ({$document->document_number}) has been returned to {$lastDepartmentName} by {$user->name} from {$currentUserDepartment}.\n\nRemarks: {$validated['remarks']}",
                    'warning',
                    $document->id
                );
            } catch (\Exception $notifError) {
                \Log::warning('Failed to notify department users on return: ' . $notifError->getMessage());
            }

            DB::commit();

            // Reload document with all relationships for response
            $document->refresh();
            $document->load(['creator', 'department']);

            return response()->json([
                'success' => true,
                'message' => 'Document returned successfully!',
                'document' => [
                    'id' => $document->id,
                    'document_number' => $document->document_number,
                    'title' => $document->title,
                    'description' => $document->description ?? '',
                    'document_type' => $document->document_type ?? 'N/A',
                    'status' => $document->status,
                    'is_priority' => $document->is_priority ?? false,
                    'department' => $document->department ? $document->department->name : 'N/A',
                    'created_by' => $document->creator ? $document->creator->name : 'Unknown',
                    'created_at' => $document->created_at ? $document->created_at->format('M d, Y h:i A') : 'N/A',
                ],
                'redirect_url' => route('documents.show', $document),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $e->errors())),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Return document error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'document_id' => $validated['document_id'] ?? null,
                'user_id' => Auth::id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to return document: ' . $e->getMessage(),
            ], 500);
        }
    }
}

