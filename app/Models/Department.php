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
}

