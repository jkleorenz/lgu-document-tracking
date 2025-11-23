<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();
        
        // Administrator Dashboard
        if ($user->hasRole('Administrator')) {
            return $this->adminDashboard();
        }
        
        // Mayor Dashboard (uses admin dashboard)
        if ($user->hasRole('Mayor')) {
            return $this->adminDashboard();
        }
        
        // LGU Staff and Department Head Dashboard
        // NOTE: LGU Staff and Department Head have identical privileges and features - they use the same dashboard
        if ($user->hasRole('LGU Staff') || $user->hasRole('Department Head')) {
            return $this->staffDashboard();
        }
        
        abort(403, 'Unauthorized access');
    }

    /**
     * Administrator Dashboard
     * Shows all system statistics and pending verifications
     */
    private function adminDashboard()
    {
        // Count all completed documents, including archived ones (completed documents are auto-archived)
        // Include documents with current status "Completed" AND archived documents that had "Completed" status before archiving
        $completedCount = Document::where(function($q) {
            // Documents with current status "Completed"
            $q->where('status', 'Completed');
            
            // OR archived documents that had "Completed" status before archiving
            // Get document IDs from status logs where the old_status is "Completed"
            // and the new_status is "Archived"
            $archivedDocIds = DB::table('document_status_logs')
                ->where('old_status', 'Completed')
                ->where('new_status', 'Archived')
                ->distinct()
                ->pluck('document_id');
            
            if ($archivedDocIds->isNotEmpty()) {
                $q->orWhere(function($subQ) use ($archivedDocIds) {
                    $subQ->where('status', 'Archived')
                         ->whereIn('id', $archivedDocIds);
                });
            }
        })->count();
        
        $data = [
            'activeDocuments' => Document::active()->count(),
            'completedDocuments' => $completedCount,
            'priorityDocuments' => Document::priority()->active()->count(),
            'archivedDocuments' => Document::archived()->count(),
            'pendingVerifications' => User::where('status', 'pending')->count(),
            'totalUsers' => User::where('status', 'verified')->count(),
            'totalDepartments' => Department::active()->count(),
            'departments' => Department::active()->orderBy('name')->get(),
            'recentDocuments' => Document::with(['creator', 'department', 'currentHandler'])
                ->active()
                ->latest()
                ->take(10)
                ->get(),
            'pendingUsers' => User::with('department')
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            'documentsByStatus' => Document::active()
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];
        
        return view('dashboard.admin', $data);
    }

    /**
     * LGU Staff and Department Head Dashboard
     * NOTE: Both roles have identical privileges and use the same dashboard
     * Shows their created documents and assigned tasks
     */
    private function staffDashboard()
    {
        $user = Auth::user();
        
        $data = [
            'myDocuments' => Document::where(function($query) use ($user) {
                    // Documents created by the user
                    $query->where('created_by', $user->id);
                })
                ->orWhere(function($query) use ($user) {
                    // Documents forwarded to their department
                    if ($user->department_id) {
                        $query->where('department_id', $user->department_id)
                              ->whereIn('status', ['Forwarded', 'Received', 'Under Review']);
                    }
                })
                ->active()
                ->count(),
            // Count return documents: documents created by user OR documents returned to user's department
            'returnDocuments' => Document::where('status', 'Return')
                ->where(function($query) use ($user) {
                    // Documents created by the user
                    $query->where('created_by', $user->id);
                    
                    // OR documents returned to user's department
                    if ($user->department_id) {
                        $query->orWhere('department_id', $user->department_id);
                    }
                })
                ->count(),
            // Count all completed documents created by user, including archived ones (completed documents are auto-archived)
            // Include documents with current status "Completed" AND archived documents that had "Completed" status before archiving
            'completedDocuments' => Document::where('created_by', $user->id)
                ->where(function($q) use ($user) {
                    // Documents with current status "Completed"
                    $q->where('status', 'Completed');
                    
                    // OR archived documents that had "Completed" status before archiving
                    // Get document IDs from status logs where the old_status is "Completed"
                    // and the new_status is "Archived", but only for documents created by this user
                    $archivedDocIds = DB::table('document_status_logs')
                        ->join('documents', 'document_status_logs.document_id', '=', 'documents.id')
                        ->where('documents.created_by', $user->id)
                        ->where('document_status_logs.old_status', 'Completed')
                        ->where('document_status_logs.new_status', 'Archived')
                        ->distinct()
                        ->pluck('document_status_logs.document_id');
                    
                    if ($archivedDocIds->isNotEmpty()) {
                        $q->orWhere(function($subQ) use ($archivedDocIds) {
                            $subQ->where('status', 'Archived')
                                 ->whereIn('id', $archivedDocIds);
                        });
                    }
                })
                ->count(),
            'recentDocuments' => Document::with(['department', 'currentHandler'])
                ->where(function($query) use ($user) {
                    // Documents created by the user
                    $query->where('created_by', $user->id);
                })
                ->orWhere(function($query) use ($user) {
                    // Documents forwarded to their department
                    if ($user->department_id) {
                        $query->where('department_id', $user->department_id)
                              ->whereIn('status', ['Forwarded', 'Received', 'Under Review']);
                    }
                })
                ->active()
                ->latest()
                ->take(10)
                ->get(),
        ];
        
        return view('dashboard.staff', $data);
    }

    /**
     * Department Head Dashboard
     * Shows documents forwarded to their department
     */
    private function departmentHeadDashboard()
    {
        $user = Auth::user();
        
        $data = [
            'departmentDocuments' => Document::where('department_id', $user->department_id)
                ->active()
                ->count(),
            'forReview' => Document::where('department_id', $user->department_id)
                ->whereIn('status', ['Received', 'Under Review', 'Forwarded'])
                ->active()
                ->count(),
            'priorityDocuments' => Document::where('department_id', $user->department_id)
                ->where('is_priority', true)
                ->active()
                ->count(),
            'recentDocuments' => Document::with(['creator', 'currentHandler'])
                ->where('department_id', $user->department_id)
                ->active()
                ->latest()
                ->take(10)
                ->get(),
        ];
        
        return view('dashboard.department-head', $data);
    }
}

