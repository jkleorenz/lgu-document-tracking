<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_number',
        'title',
        'description',
        'document_type',
        'qr_code_path',
        'created_by',
        'current_handler_id',
        'department_id',
        'status',
        'is_priority',
        'archived_at',
    ];

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent document_number from being changed after creation
        static::updating(function ($document) {
            if ($document->isDirty('document_number') && $document->getOriginal('document_number')) {
                $document->document_number = $document->getOriginal('document_number');
            }
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_priority' => 'boolean',
        'archived_at' => 'datetime',
    ];

    /**
     * Generate a unique document number
     */
    public static function generateDocumentNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = 'DOC-' . $year . $month;
        
        // Include soft-deleted documents to avoid duplicate numbers
        $lastDoc = self::withTrashed()
            ->where('document_number', 'like', $prefix . '%')
            ->orderBy('document_number', 'desc')
            ->first();
        
        if ($lastDoc) {
            $lastNumber = (int) substr($lastDoc->document_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        // Ensure uniqueness by checking if the generated number already exists
        $documentNumber = $prefix . '-' . $newNumber;
        while (self::withTrashed()->where('document_number', $documentNumber)->exists()) {
            $lastNumber++;
            $newNumber = str_pad($lastNumber, 4, '0', STR_PAD_LEFT);
            $documentNumber = $prefix . '-' . $newNumber;
        }
        
        return $documentNumber;
    }

    /**
     * Get the user who created this document
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the current handler of this document
     */
    public function currentHandler()
    {
        return $this->belongsTo(User::class, 'current_handler_id');
    }

    /**
     * Get the department this document is assigned to
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all status logs for this document
     */
    public function statusLogs()
    {
        return $this->hasMany(DocumentStatusLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get notifications related to this document
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if document is archived
     */
    public function isArchived(): bool
    {
        return !is_null($this->archived_at);
    }

    /**
     * Check if document is priority
     */
    public function isPriority(): bool
    {
        return $this->is_priority === true;
    }

    /**
     * Scope to get only priority documents
     */
    public function scopePriority($query)
    {
        return $query->where('is_priority', true);
    }

    /**
     * Scope to get only archived documents
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Scope to get only active (non-archived) documents
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }
}

