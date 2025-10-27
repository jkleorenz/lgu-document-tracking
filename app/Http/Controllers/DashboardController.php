<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        // LGU Staff Dashboard
        if ($user->hasRole('LGU Staff')) {
            return $this->staffDashboard();
        }
        
        // Department Head Dashboard
        if ($user->hasRole('Department Head')) {
            return $this->departmentHeadDashboard();
        }
        
        abort(403, 'Unauthorized access');
    }

    /**
     * Administrator Dashboard
     * Shows all system statistics and pending verifications
     */
    private function adminDashboard()
    {
        $data = [
            'activeDocuments' => Document::active()->count(),
            'approvedDocuments' => Document::where('status', 'Approved')->count(),
            'priorityDocuments' => Document::priority()->active()->count(),
            'archivedDocuments' => Document::archived()->count(),
            'pendingVerifications' => User::where('status', 'pending')->count(),
            'totalUsers' => User::where('status', 'verified')->count(),
            'totalDepartments' => Department::active()->count(),
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
     * LGU Staff Dashboard
     * Shows their created documents and assigned tasks
     */
    private function staffDashboard()
    {
        $user = Auth::user();
        
        $data = [
            'myDocuments' => Document::where('created_by', $user->id)
                ->active()
                ->count(),
            'pendingDocuments' => Document::where('created_by', $user->id)
                ->where('status', 'Pending')
                ->count(),
            'approvedDocuments' => Document::where('created_by', $user->id)
                ->where('status', 'Approved')
                ->active()
                ->count(),
            'recentDocuments' => Document::with(['department', 'currentHandler'])
                ->where('created_by', $user->id)
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
        
        // Count documents handled by this department head
        $handlingDocuments = \DB::table('document_status_logs')
            ->where('updated_by', $user->id)
            ->distinct('document_id')
            ->count('document_id');
        
        $data = [
            'departmentDocuments' => Document::where('department_id', $user->department_id)
                ->active()
                ->count(),
            'forReview' => Document::where('department_id', $user->department_id)
                ->whereIn('status', ['Received', 'Under Review', 'Forwarded'])
                ->count(),
            'priorityDocuments' => Document::where('department_id', $user->department_id)
                ->where('is_priority', true)
                ->active()
                ->count(),
            'handlingDocuments' => $handlingDocuments,
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

