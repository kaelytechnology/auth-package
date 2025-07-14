<?php

namespace Kaely\AuthPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
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
     * Get the users that belong to this branch.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'branches_users');
    }

    /**
     * Get the user who created this branch.
     */
    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_add');
    }

    /**
     * Get the user who last edited this branch.
     */
    public function userEdit()
    {
        return $this->belongsTo(User::class, 'user_edit');
    }

    /**
     * Get the user who deleted this branch.
     */
    public function userDeleted()
    {
        return $this->belongsTo(User::class, 'user_deleted');
    }

    /**
     * Check if branch is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Activate branch.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate branch.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
} 