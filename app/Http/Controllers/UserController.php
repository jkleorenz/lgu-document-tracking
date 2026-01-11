<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of users (Admin only)
     */
    public function index(Request $request)
    {
        $this->authorize('verify-users');

        $query = User::with(['department', 'roles']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);
        $pendingCount = User::where('status', 'pending')->count();

        return view('users.index', compact('users', 'pendingCount'));
    }

    /**
     * Display pending user verifications
     */
    public function pendingVerifications()
    {
        $this->authorize('verify-users');

        $pendingUsers = User::with(['department', 'roles'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('users.pending', compact('pendingUsers'));
    }

    /**
     * Verify a user account
     */
    public function verify(User $user)
    {
        $this->authorize('verify-users');

        $oldStatus = $user->status;
        $user->update(['status' => 'verified']);

        // Audit log
        AuditLog::log('user.verified', auth()->id(), User::class, $user->id, "User {$user->name} ({$user->email}) verified by administrator", ['status' => $oldStatus], ['status' => 'verified'], request()->ip(), request()->userAgent());

        // Notify the user
        $this->notificationService->notifyAccountVerified($user->id);

        return back()->with('success', "User {$user->name} has been verified successfully!");
    }

    /**
     * Reject a user account
     */
    public function reject(User $user)
    {
        $this->authorize('verify-users');

        $oldStatus = $user->status;
        $user->update(['status' => 'rejected']);

        // Audit log
        AuditLog::log('user.rejected', auth()->id(), User::class, $user->id, "User {$user->name} ({$user->email}) rejected by administrator", ['status' => $oldStatus], ['status' => 'rejected'], request()->ip(), request()->userAgent());

        // Notify the user about rejection
        $this->notificationService->notifyAccountRejected($user->id);

        return back()->with('success', "User {$user->name} has been rejected.");
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $this->authorize('verify-users');

        $departments = Department::active()->orderBy('name')->get();
        $roles = ['Administrator', 'LGU Staff', 'Department Head'];

        return view('users.create', compact('departments', 'roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $this->authorize('verify-users');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'role' => ['required', 'string'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'],
            'status' => 'verified', // Admin-created users are auto-verified
        ]);

        $user->assignRole($validated['role']);

        // Audit log
        AuditLog::log('user.created', auth()->id(), User::class, $user->id, "User {$user->name} ({$user->email}) created by administrator", null, ['name' => $user->name, 'email' => $user->email, 'role' => $validated['role']], request()->ip(), request()->userAgent());

        return redirect()->route('users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $this->authorize('verify-users');

        $user->load(['department', 'roles', 'createdDocuments', 'statusLogs']);
        
        // Get handling documents based on role
        $handlingDocuments = $user->handlingDocuments()->get();

        return view('users.show', compact('user', 'handlingDocuments'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $this->authorize('verify-users');

        $departments = Department::active()->orderBy('name')->get();
        $roles = ['Administrator', 'LGU Staff', 'Department Head'];

        return view('users.edit', compact('user', 'departments', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('verify-users');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'role' => ['required', 'string'],
            'status' => ['required', 'in:pending,verified,rejected'],
        ]);

        $oldValues = $user->only(['name', 'email', 'phone', 'department_id', 'status']);
        $oldRole = $user->roles->first()?->name;

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'],
            'status' => $validated['status'],
        ]);

        // Update role
        $user->syncRoles([$validated['role']]);

        $newValues = $user->fresh()->only(['name', 'email', 'phone', 'department_id', 'status']);
        $newValues['role'] = $validated['role'];

        // Audit log
        AuditLog::log('user.updated', auth()->id(), User::class, $user->id, "User {$user->name} ({$user->email}) updated by administrator", array_merge($oldValues, ['role' => $oldRole]), $newValues, request()->ip(), request()->userAgent());

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Delete the specified user
     */
    public function destroy(User $user)
    {
        $this->authorize('verify-users');

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $userData = $user->only(['name', 'email', 'status']);
        $user->delete();

        // Audit log
        AuditLog::log('user.deleted', auth()->id(), User::class, $user->id, "User {$userData['name']} ({$userData['email']}) deleted by administrator", $userData, null, request()->ip(), request()->userAgent());

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully!');
    }
}

