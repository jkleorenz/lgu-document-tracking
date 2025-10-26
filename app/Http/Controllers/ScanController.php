<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentStatusLog;
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
                ->with(['creator', 'department', 'currentHandler', 'statusLogs.updatedBy'])
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
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'document_number' => ['required', 'string'],
        ]);

        // Find document by document number
        $document = Document::where('document_number', $validated['document_number'])
            ->with(['creator', 'department', 'currentHandler', 'statusLogs.updatedBy'])
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.',
            ], 404);
        }

        // Check if user has permission to view this document
        $user = Auth::user();
        
        if (!$user->hasRole('Administrator')) {
            if ($user->hasRole('LGU Staff') && $document->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to access this document.',
                ], 403);
            }
            if ($user->hasRole('Department Head') && $document->department_id !== $user->department_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This document is not assigned to your department.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Document found successfully.',
            'document' => [
                'id' => $document->id,
                'document_number' => $document->document_number,
                'title' => $document->title,
                'description' => $document->description,
                'status' => $document->status,
                'is_priority' => $document->is_priority,
                'department' => $document->department->name,
                'created_by' => $document->creator->name,
                'current_handler' => $document->currentHandler ? $document->currentHandler->name : 'Unassigned',
                'created_at' => $document->created_at->format('M d, Y h:i A'),
            ],
            'redirect_url' => route('documents.show', $document),
        ]);
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
                'current_handler_id' => Auth::id(),
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
}

