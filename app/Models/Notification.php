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
     * Create a new notification (with enhanced duplicate prevention)
     */
    public static function createNotification($userId, $title, $message, $type = 'info', $documentId = null)
    {
        // Enhanced duplicate prevention: Check for similar notifications for the same document and user
        // within the last 30 seconds (increased to catch more duplicates)
        $recentDuplicate = self::where('user_id', $userId)
            ->where('document_id', $documentId)
            ->where('created_at', '>=', now()->subSeconds(30))
            ->where(function($query) use ($title, $message) {
                // Check for exact match (title and message)
                $query->where(function($q) use ($title, $message) {
                    $q->where('title', $title)
                      ->where('message', $message);
                })
                // OR check for similar notifications (same document, similar event)
                // Extract document number and event keywords from message
                ->orWhere(function($q) use ($message) {
                    if (preg_match('/\(DOC-[^)]+\)/', $message, $matches)) {
                        $docNumber = $matches[0];
                        // Check for common event keywords
                        $eventKeywords = ['received', 'forwarded', 'returned', 'completed', 'archived'];
                        $hasEventKeyword = false;
                        foreach ($eventKeywords as $keyword) {
                            if (stripos($message, $keyword) !== false) {
                                $hasEventKeyword = true;
                                break;
                            }
                        }
                        if ($hasEventKeyword) {
                            $q->where('message', 'like', "%{$docNumber}%");
                        }
                    }
                });
            })
            ->first();
        
        // If duplicate exists within 30 seconds, don't create another one
        // Update timestamp to current time to fix "17 minutes ago" issue
        // This ensures the notification shows the correct "time ago" when displayed
        if ($recentDuplicate) {
            // Always update timestamp to now() so it shows correctly
            // This prevents showing "17 minutes ago" for a notification that was just created
            $recentDuplicate->update([
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $recentDuplicate->refresh();
            return $recentDuplicate;
        }
        
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

