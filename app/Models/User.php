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
        // For administrators: count all pending documents and priority documents
        if ($this->hasRole('Administrator')) {
            return Document::active()
                ->where(function($query) {
                    $query->where('status', 'Pending')
                          ->orWhere('is_priority', true);
                })
                ->count();
        }
        
        // For department heads: count documents in their department needing action
        if ($this->hasRole('Department Head') && $this->department_id) {
            return Document::active()
                ->where('department_id', $this->department_id)
                ->whereIn('status', ['Pending', 'Received', 'Under Review', 'Forwarded'])
                ->count();
        }
        
        // For LGU staff: count their pending documents
        if ($this->hasRole('LGU Staff')) {
            return Document::active()
                ->where('created_by', $this->id)
                ->whereIn('status', ['Pending', 'Under Review'])
                ->count();
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
}

