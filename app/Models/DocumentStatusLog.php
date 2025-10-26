<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentStatusLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'updated_by',
        'old_status',
        'new_status',
        'remarks',
        'action_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'action_date' => 'datetime',
    ];

    /**
     * Get the document this log belongs to
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who made this status update
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Create a new status log entry
     */
    public static function createLog($documentId, $userId, $oldStatus, $newStatus, $remarks = null)
    {
        return self::create([
            'document_id' => $documentId,
            'updated_by' => $userId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'remarks' => $remarks,
            'action_date' => now(),
        ]);
    }
}

