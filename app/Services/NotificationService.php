<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

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
     */
    public function notifyDepartmentUsers($departmentId, $title, $message, $type = 'info', $documentId = null)
    {
        $users = User::where('department_id', $departmentId)
            ->where('status', 'verified')
            ->pluck('id')
            ->toArray();
        
        Notification::notifyUsers($users, $title, $message, $type, $documentId);
    }

    /**
     * Notify all administrators
     */
    public function notifyAdministrators($title, $message, $type = 'info', $documentId = null)
    {
        $admins = User::role('Administrator')
            ->where('status', 'verified')
            ->pluck('id')
            ->toArray();
        
        Notification::notifyUsers($admins, $title, $message, $type, $documentId);
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
}

