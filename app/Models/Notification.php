<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'document_id',
        'title',
        'message',
        'type',
        'is_read',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user this notification belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the document related to this notification
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Scope to get only unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Create a new notification
     */
    public static function createNotification($userId, $title, $message, $type = 'info', $documentId = null)
    {
        return self::create([
            'user_id' => $userId,
            'document_id' => $documentId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
        ]);
    }

    /**
     * Notify multiple users
     */
    public static function notifyUsers($userIds, $title, $message, $type = 'info', $documentId = null)
    {
        foreach ($userIds as $userId) {
            self::createNotification($userId, $title, $message, $type, $documentId);
        }
    }
}

