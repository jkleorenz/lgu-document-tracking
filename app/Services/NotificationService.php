<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Department;
use App\Models\DocumentStatusLog;

class NotificationService
{
    /**
     * Notify user about new document
     */
    public function notifyNewDocument($document, $recipientId)
    {
        return Notification::createNotification(
            $recipientId,
            'New Document Created',
            "Document '{$document->title}' has been created and assigned to {$document->department->name}.",
            'info',
            $document->id
        );
    }

    /**
     * Notify user about document status update
     */
    public function notifyStatusUpdate($document, $recipientId, $newStatus)
    {
        return Notification::createNotification(
            $recipientId,
            'Document Status Updated',
            "Document '{$document->title}' status has been updated to {$newStatus}.",
            'success',
            $document->id
        );
    }

    /**
     * Notify user about priority document
     */
    public function notifyPriorityDocument($document, $recipientId)
    {
        return Notification::createNotification(
            $recipientId,
            'Priority Document',
            "Document '{$document->title}' has been marked as PRIORITY. Immediate attention required!",
            'warning',
            $document->id
        );
    }

    /**
     * Notify user about document forwarding
     */
    public function notifyDocumentForwarded($document, $recipientId)
    {
        return Notification::createNotification(
            $recipientId,
            'Document Forwarded',
            "Document '{$document->title}' has been forwarded to you.",
            'info',
            $document->id
        );
    }

    /**
     * Notify user about document archiving
     */
    public function notifyDocumentArchived($document, $recipientId)
    {
        return Notification::createNotification(
            $recipientId,
            'Document Archived',
            "Document '{$document->title}' has been archived.",
            'success',
            $document->id
        );
    }

    /**
     * Notify department users
     * @param int $departmentId The department ID
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type Notification type (info, success, warning, danger)
     * @param int|null $documentId Document ID
     * @param array|null $excludeUserIds User IDs to exclude from notification (e.g., creator)
     */
    public function notifyDepartmentUsers($departmentId, $title, $message, $type = 'info', $documentId = null, $excludeUserIds = null)
    {
        $users = User::where('department_id', $departmentId)
            ->where('status', 'verified');
        
        // Exclude specific users if provided (e.g., creator should not receive forwarding notifications)
        if ($excludeUserIds) {
            if (is_array($excludeUserIds)) {
                $users = $users->whereNotIn('id', $excludeUserIds);
            } else {
                $users = $users->where('id', '!=', $excludeUserIds);
            }
        }
        
        $userIds = $users->pluck('id')->toArray();
        
        Notification::notifyUsers($userIds, $title, $message, $type, $documentId);
    }

    /**
     * Notify all administrators
     */
    public function notifyAdministrators($title, $message, $type = 'info', $documentId = null, $excludeUserIds = null)
    {
        $admins = User::role('Administrator')
            ->where('status', 'verified');
        
        // Exclude specific users if provided (to avoid duplicate notifications)
        // Can be a single user ID or an array of user IDs
        if ($excludeUserIds) {
            if (is_array($excludeUserIds)) {
                $admins = $admins->whereNotIn('id', $excludeUserIds);
            } else {
                $admins = $admins->where('id', '!=', $excludeUserIds);
            }
        }
        
        $adminIds = $admins->pluck('id')->toArray();
        
        Notification::notifyUsers($adminIds, $title, $message, $type, $documentId);
    }

    /**
     * Notify user about account verification
     */
    public function notifyAccountVerified($userId)
    {
        return Notification::createNotification(
            $userId,
            'Account Verified',
            'Your account has been verified by an administrator. You can now login and use the system.',
            'success',
            null
        );
    }

    /**
     * Notify user about account rejection
     */
    public function notifyAccountRejected($userId)
    {
        return Notification::createNotification(
            $userId,
            'Account Rejected',
            'Your account registration has been rejected. Please contact the administrator for more information.',
            'danger',
            null
        );
    }

    /**
     * Notify document creator
     */
    public function notifyCreator($document, $title, $message, $type = 'info')
    {
        if ($document->created_by) {
            Notification::createNotification(
                $document->created_by,
                $title,
                $message,
                $type,
                $document->id
            );
        }
    }

    /**
     * Get the last department that handled a document (for return notifications)
     */
    private function getLastHandlingDepartment($document, $currentDepartmentId)
    {
        // Load creator if not already loaded
        if (!$document->relationLoaded('creator') && $document->created_by) {
            $document->load('creator');
        }
        
        // Get status logs ordered by date (newest first)
        $statusLogs = DocumentStatusLog::where('document_id', $document->id)
            ->with('updatedBy.department')
            ->orderBy('action_date', 'desc')
            ->get();
        
        // Find the first department in logs that's different from current department
        foreach ($statusLogs as $log) {
            if ($log->updatedBy && $log->updatedBy->department_id && 
                $log->updatedBy->department_id != $currentDepartmentId) {
                // Use the already loaded department relationship if available
                if ($log->updatedBy->relationLoaded('department') && $log->updatedBy->department) {
                    return $log->updatedBy->department;
                }
                return Department::find($log->updatedBy->department_id);
            }
        }
        
        // If no previous department found, check creator's department as fallback
        if ($document->creator && $document->creator->department_id && 
            $document->creator->department_id != $currentDepartmentId) {
            // Use the already loaded department relationship if available
            if ($document->creator->relationLoaded('department') && $document->creator->department) {
                return $document->creator->department;
            }
            return Department::find($document->creator->department_id);
        }
        
        return null;
    }

    /**
     * EVENT 1: Document Forwarded
     * Refined Rules:
     * - Notify: Non-creator users in Receiving Department, Administrator
     * - Do NOT notify: Creator (even if they're in the receiving department - they only get status change notifications)
     */
    public function onDocumentForwarded($document, $oldDepartment, $newDepartment, $forwarder)
    {
        $forwarderRole = $forwarder->roles->first()->name ?? 'User';
        $priorityText = $document->is_priority ? ' [PRIORITY]' : '';
        $notificationType = $document->is_priority ? 'warning' : 'info';
        
        // Handle old department name (could be null or dummy object)
        $oldDepartmentName = 'System';
        if ($oldDepartment && (is_object($oldDepartment) && isset($oldDepartment->name))) {
            $oldDepartmentName = $oldDepartment->name;
        } elseif ($oldDepartment && method_exists($oldDepartment, 'getAttribute')) {
            // It's a model instance
            $oldDepartmentName = $oldDepartment->name;
        }
        
        // Load creator to check if they're in the receiving department
        $creatorId = null;
        if ($document->created_by) {
            if (!$document->relationLoaded('creator')) {
                $document->load('creator');
            }
            $creator = $document->creator;
            // If creator is in the receiving department, exclude them from department notification
            // (Creators only get status change notifications, not forwarding notifications)
            if ($creator && $creator->department_id == $newDepartment->id) {
                $creatorId = $document->created_by;
            }
        }
        
        // A. Notify Forwarded/Receiving Department (EXCLUDE creator)
        // Only non-creator users in the receiving department get this notification
        $this->notifyDepartmentUsers(
            $newDepartment->id,
            'Document Forwarded to ' . $newDepartment->name . $priorityText,
            "{$document->title} ({$document->document_number}) forwarded to your department by {$forwarder->name}" . 
            ($oldDepartmentName !== 'System' ? " from {$oldDepartmentName}" : "") . ".",
            $notificationType,
            $document->id,
            $creatorId ? [$creatorId] : null
        );
        
        // B. Notify Administrator (all events)
        // Exclude forwarder if they're an admin to avoid duplicate (they already know they forwarded it)
        $excludeAdminIds = [];
        if ($forwarder->hasRole('Administrator')) {
            $excludeAdminIds[] = $forwarder->id;
        }
        $this->notifyAdministrators(
            'Document Forwarded' . $priorityText,
            "{$document->title} ({$document->document_number}) forwarded" . 
            ($oldDepartmentName !== 'System' ? " from {$oldDepartmentName}" : "") . 
            " to {$newDepartment->name} by {$forwarder->name}.",
            $notificationType,
            $document->id,
            !empty($excludeAdminIds) ? $excludeAdminIds : null
        );
    }

    /**
     * EVENT 2: Document Received via QR Scan
     * Rules:
     * - Notify: Creator, Administrator
     * - Do NOT notify: Receiving Department (they already know - they scanned it)
     */
    public function onDocumentReceivedViaQRScan($document, $scanner, $scannerDepartment)
    {
        $scannerIsAdmin = $scanner->hasRole('Administrator');
        $scannerIsCreator = $document->created_by == $scanner->id;
        
        // Build the message once
        $message = "{$document->title} ({$document->document_number}) received at {$scannerDepartment} by {$scanner->name}.";
        
        // Load creator efficiently (check if already loaded, otherwise load it)
        $creator = null;
        $creatorIsAdmin = false;
        if ($document->created_by) {
            if ($document->relationLoaded('creator')) {
                $creator = $document->creator;
            } else {
                $creator = User::find($document->created_by);
            }
            $creatorIsAdmin = $creator && $creator->hasRole('Administrator');
        }
        
        // A. Notify Creator (if scanner is not the creator AND creator is not an admin)
        // If creator is an admin, they'll get notified as admin below, so skip creator notification to avoid duplicate
        if ($document->created_by && !$scannerIsCreator && !$creatorIsAdmin) {
            $this->notifyCreator(
                $document,
                'Document Received',
                $message,
                'info'
            );
        }
        
        // B. Notify Administrator (all events)
        // Exclude users who already received notifications:
        // 1. Scanner if they're an admin (they already know - they scanned it)
        // 2. Creator if they're an admin (they already got notified above, or will get notified here)
        $excludeUserIds = [];
        if ($scannerIsAdmin) {
            $excludeUserIds[] = $scanner->id;
        }
        // If creator is admin and not the scanner, exclude them (they'll get admin notification)
        if ($creatorIsAdmin && !$scannerIsCreator) {
            $excludeUserIds[] = $document->created_by;
        }
        // Remove duplicates
        $excludeUserIds = array_unique($excludeUserIds);
        
        $this->notifyAdministrators(
            'Document Received via QR Scan',
            $message,
            'info',
            $document->id,
            !empty($excludeUserIds) ? $excludeUserIds : null
        );
    }

    /**
     * EVENT 3: Document Returned
     * Refined Rules:
     * - Notify: Non-creator users in the department being returned TO, Creator, Administrator
     * - Do NOT notify: Last handling department (we notify the department being returned TO instead)
     */
    public function onDocumentReturned($document, $returnedToDepartment, $returnedBy, $remarks)
    {
        $returnedByDepartment = $returnedBy->department ? $returnedBy->department->name : 'Unknown Department';
        
        // Load creator efficiently
        $creator = null;
        $creatorIsAdmin = false;
        $creatorId = null;
        if ($document->created_by) {
            if (!$document->relationLoaded('creator')) {
                $document->load('creator');
            }
            $creator = $document->creator;
            $creatorIsAdmin = $creator && $creator->hasRole('Administrator');
            $creatorId = $document->created_by;
        }
        
        // A. Notify Department Being Returned TO (EXCLUDE creator)
        // Only non-creator users in the department being returned to get this notification
        // If creator is in that department, they'll get the creator notification below instead
        if ($returnedToDepartment) {
            $this->notifyDepartmentUsers(
                $returnedToDepartment->id,
                'Document Returned to ' . $returnedToDepartment->name,
                "{$document->title} ({$document->document_number}) returned to your department by {$returnedBy->name} from {$returnedByDepartment}.\n\nRemarks: {$remarks}",
                'warning',
                $document->id,
                $creatorId && $creator && $creator->department_id == $returnedToDepartment->id ? [$creatorId] : null
            );
        }
        
        // B. Notify Creator (status change notification - separate from department notification)
        // Creator gets notified regardless of which department they're in
        if ($document->created_by && $document->created_by != $returnedBy->id && !$creatorIsAdmin) {
            $this->notifyCreator(
                $document,
                'Document Returned',
                "{$document->title} ({$document->document_number}) returned to {$returnedToDepartment->name} by {$returnedBy->name}.\n\nRemarks: {$remarks}",
                'warning'
            );
        }
        
        // C. Notify Administrator (all events)
        // Exclude the returner if they're an admin, and exclude creator if they're also an admin
        $excludeAdminIds = [];
        if ($returnedBy->hasRole('Administrator')) {
            $excludeAdminIds[] = $returnedBy->id;
        }
        if ($creatorIsAdmin) {
            $excludeAdminIds[] = $document->created_by;
        }
        $excludeAdminIds = array_unique($excludeAdminIds);
        $this->notifyAdministrators(
            'Document Returned',
            "{$document->title} ({$document->document_number}) returned to {$returnedToDepartment->name} by {$returnedBy->name} from {$returnedByDepartment}.\n\nRemarks: {$remarks}",
            'warning',
            $document->id,
            !empty($excludeAdminIds) ? $excludeAdminIds : null
        );
    }

    /**
     * EVENT 4: Document Completed / Archived-Completed
     * Rules:
     * - Notify: Creator, Administrator
     * - Status: 'Completed' with archived_at set
     */
    public function onDocumentCompleted($document, $completedBy)
    {
        // Load creator efficiently
        $creator = null;
        $creatorIsAdmin = false;
        if ($document->created_by) {
            if ($document->relationLoaded('creator')) {
                $creator = $document->creator;
            } else {
                $creator = User::find($document->created_by);
            }
            $creatorIsAdmin = $creator && $creator->hasRole('Administrator');
        }
        
        // A. Notify Creator (if completed by someone else AND creator is not an admin)
        // If creator is an admin, they'll get notified as admin below, so skip creator notification to avoid duplicate
        if ($document->created_by && $document->created_by != $completedBy->id && !$creatorIsAdmin) {
            $this->notifyCreator(
                $document,
                'Document Completed',
                "{$document->title} ({$document->document_number}) completed and archived by {$completedBy->name}.",
                'success'
            );
        }
        
        // B. Notify Administrator (all events)
        // Exclude the completer if they're an admin, and exclude creator if they're also an admin
        $excludeUserIds = [];
        if ($completedBy->hasRole('Administrator')) {
            $excludeUserIds[] = $completedBy->id;
        }
        if ($creatorIsAdmin) {
            $excludeUserIds[] = $document->created_by;
        }
        $excludeUserIds = array_unique($excludeUserIds);
        $this->notifyAdministrators(
            'Document Completed',
            "{$document->title} ({$document->document_number}) completed and archived by {$completedBy->name}.",
            'success',
            $document->id,
            !empty($excludeUserIds) ? $excludeUserIds : null
        );
    }

    /**
     * EVENT 5: Document Archived-Not Completed
     * Rules:
     * - Notify: Creator (optional), Administrator
     * - Status: 'Archived' with archived_at set (manually archived, not completed)
     */
    public function onDocumentArchivedNotCompleted($document, $archivedBy)
    {
        // Load creator efficiently
        $creator = null;
        $creatorIsAdmin = false;
        if ($document->created_by) {
            if ($document->relationLoaded('creator')) {
                $creator = $document->creator;
            } else {
                $creator = User::find($document->created_by);
            }
            $creatorIsAdmin = $creator && $creator->hasRole('Administrator');
        }
        
        // A. Notify Creator (optional awareness, but exclude if creator is an admin)
        // If creator is an admin, they'll get notified as admin below, so skip creator notification to avoid duplicate
        if ($document->created_by && $document->created_by != $archivedBy->id && !$creatorIsAdmin) {
            $this->notifyCreator(
                $document,
                'Document Archived',
                "{$document->title} ({$document->document_number}) archived by {$archivedBy->name}.",
                'info'
            );
        }
        
        // B. Notify Administrator (all events)
        // Exclude the archiver if they're an admin, and exclude creator if they're also an admin
        $excludeUserIds = [];
        if ($archivedBy->hasRole('Administrator')) {
            $excludeUserIds[] = $archivedBy->id;
        }
        if ($creatorIsAdmin) {
            $excludeUserIds[] = $document->created_by;
        }
        $excludeUserIds = array_unique($excludeUserIds);
        $this->notifyAdministrators(
            'Document Archived (Not Completed)',
            "{$document->title} ({$document->document_number}) archived by {$archivedBy->name}.",
            'info',
            $document->id,
            !empty($excludeUserIds) ? $excludeUserIds : null
        );
    }
}

