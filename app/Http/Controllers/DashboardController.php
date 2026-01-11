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
        // Count all completed documents, including archived-completed ones
        // Completed documents have status='Completed' (may or may not have archived_at set)
        $completedCount = Document::where('status', 'Completed')->count();
        
        $data = [
            // Active documents include Forwarded, Received, Under Review, Pending, Return
            'activeDocuments' => Document::active()
                ->whereIn('status', ['Forwarded', 'Received', 'Under Review', 'Pending', 'Return'])
                ->count(),
            'completedDocuments' => $completedCount,
            'priorityDocuments' => Document::priority()
                ->active()
                ->whereIn('status', ['Forwarded', 'Received', 'Under Review', 'Pending', 'Return'])
                ->count(),
            // Count archived-completed documents (status='Completed' with archived_at set)
            'archivedDocuments' => Document::where('status', 'Completed')
                ->whereNotNull('archived_at')
                ->count(),
            'pendingVerifications' => User::where('status', 'pending')->count(),
            'totalUsers' => User::where('status', 'verified')->count(),
            'totalDepartments' => Department::active()->count(),
            'departments' => Department::active()->orderBy('name')->get(),
            'recentDocuments' => Document::with(['creator', 'department', 'currentHandler'])
                ->active()
                ->whereIn('status', ['Forwarded', 'Received', 'Under Review', 'Pending', 'Return'])
                ->latest()
                ->take(10)
                ->get(),
            'pendingUsers' => User::with('department')
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            'documentsByStatus' => Document::active()
                ->whereIn('status', ['Forwarded', 'Received', 'Under Review', 'Pending', 'Return'])
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
            // Count completed documents:
            // 1. Documents created by user with status='Completed' (includes archived-completed)
            // 2. Documents completed by user's department with status='Completed' (includes archived-completed)
            'completedDocuments' => Document::where('status', 'Completed')
                ->where(function($q) use ($user) {
                    // Documents created by the user
                    $q->where('created_by', $user->id)
                      // OR documents from user's department
                      ->orWhere(function($deptQ) use ($user) {
                          if ($user->department_id) {
                              $deptQ->where('department_id', $user->department_id);
                          }
                      });
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

