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
     * Get documents currently assigned to this user
     */
    public function handlingDocuments()
    {
        return $this->hasMany(Document::class, 'current_handler_id');
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
     * Get document status logs created by this user
     */
    public function statusLogs()
    {
        return $this->hasMany(DocumentStatusLog::class, 'updated_by');
    }
}

