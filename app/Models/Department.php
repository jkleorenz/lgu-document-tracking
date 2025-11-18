<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'head_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the department head (user)
     */
    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    /**
     * Get all users in this department
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all documents assigned to this department
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Scope to get only active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the display name with code (ensures code is not duplicated)
     * Removes any existing code from the name before appending the code
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        // Remove any occurrence of the code in parentheses from the name
        $cleanedName = preg_replace('/\s*\(' . preg_quote($this->code, '/') . '\)\s*/', '', $this->name);
        $cleanedName = trim($cleanedName);
        
        // Append the code
        return $cleanedName . ' (' . $this->code . ')';
    }

    /**
     * Get clean name without code
     *
     * @return string
     */
    public function getCleanNameAttribute(): string
    {
        // Remove any occurrence of the code in parentheses from the name
        $cleanedName = preg_replace('/\s*\(' . preg_quote($this->code, '/') . '\)\s*/', '', $this->name);
        return trim($cleanedName);
    }
}

