<?php

namespace Kaely\AuthPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'user_add',
        'user_edit',
        'user_deleted'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'user_add' => 'integer',
        'user_edit' => 'integer',
        'user_deleted' => 'integer',
    ];

    /**
     * Get the users that belong to this department.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'departments_users');
    }

    /**
     * Get the user who created this department.
     */
    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_add');
    }

    /**
     * Get the user who last edited this department.
     */
    public function userEdit()
    {
        return $this->belongsTo(User::class, 'user_edit');
    }

    /**
     * Get the user who deleted this department.
     */
    public function userDeleted()
    {
        return $this->belongsTo(User::class, 'user_deleted');
    }

    /**
     * Check if department is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Activate department.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate department.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
} 