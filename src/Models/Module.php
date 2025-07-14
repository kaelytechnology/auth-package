<?php

namespace Kaely\AuthPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'route',
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
     * Get the permissions that belong to this module.
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Get the user who created this module.
     */
    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_add');
    }

    /**
     * Get the user who last edited this module.
     */
    public function userEdit()
    {
        return $this->belongsTo(User::class, 'user_edit');
    }

    /**
     * Get the user who deleted this module.
     */
    public function userDeleted()
    {
        return $this->belongsTo(User::class, 'user_deleted');
    }

    /**
     * Check if module is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Activate module.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate module.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
} 