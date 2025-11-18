<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'department_id',
        'status',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user account is verified by admin
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if user account is pending verification
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the department that the user belongs to
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the profile picture URL
     */
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return null;
    }

    /**
     * Get documents created by this user
     */
    public function createdDocuments()
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    /**
     * Get notifications for this user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    /**
     * Get unread notifications count
     */
    public function unreadNotificationsCount()
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    /**
     * Get count of documents requiring user's attention
     */
    public function pendingDocumentsCount()
    {
        // For administrators: count all active documents
        if ($this->hasRole('Administrator')) {
            return Document::active()->count();
        }
        
        // For department heads: count documents in their department needing action
        if ($this->hasRole('Department Head') && $this->department_id) {
            return Document::active()
                ->where('department_id', $this->department_id)
                ->whereIn('status', ['Pending', 'Received', 'Under Review', 'Forwarded'])
                ->count();
        }
        
        // For LGU staff: count documents forwarded to their department that require action
        // (exclude documents they created themselves, as those are waiting on others, not them)
        if ($this->hasRole('LGU Staff')) {
            if ($this->department_id) {
                return Document::active()
                    ->where('department_id', $this->department_id)
                    ->whereIn('status', ['Forwarded', 'Received', 'Under Review'])
                    ->count();
            }
        }
        
        return 0;
    }

    /**
     * Get document status logs created by this user
     */
    public function statusLogs()
    {
        return $this->hasMany(DocumentStatusLog::class, 'updated_by');
    }

    /**
     * Get documents that this user is currently handling (via their department)
     */
    public function handlingDocuments()
    {
        // For Department Heads: documents in their department
        if ($this->hasRole('Department Head') && $this->department_id) {
            return Document::where('department_id', $this->department_id)
                ->whereIn('status', ['Pending', 'Received', 'Under Review', 'Forwarded'])
                ->latest();
        }
        
        // For LGU Staff: their pending documents AND documents forwarded to their department
        if ($this->hasRole('LGU Staff')) {
            return Document::where(function($query) {
                    // Documents created by the user
                    $query->where('created_by', $this->id)
                          ->whereIn('status', ['Pending', 'Under Review']);
                })
                ->orWhere(function($query) {
                    // Documents forwarded to their department (even if not created by them)
                    if ($this->department_id) {
                        $query->where('department_id', $this->department_id)
                              ->whereIn('status', ['Forwarded', 'Received', 'Under Review']);
                    }
                })
                ->latest();
        }
        
        // For Administrators: all active documents needing attention
        if ($this->hasRole('Administrator')) {
            return Document::active()
                ->whereIn('status', ['Pending', 'Under Review'])
                ->latest();
        }
        
        // Default: empty collection
        return Document::whereRaw('1 = 0');
    }
}

