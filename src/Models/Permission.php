<?php

namespace Kaely\AuthPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_id',
        'name',
        'slug',
        'description',
        'user_add',
        'user_edit',
        'user_deleted'
    ];

    protected $casts = [
        'module_id' => 'integer',
        'user_add' => 'integer',
        'user_edit' => 'integer',
        'user_deleted' => 'integer',
    ];

    /**
     * Get the module that owns the permission.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the roles that have this permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Get the user who created this permission.
     */
    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_add');
    }

    /**
     * Get the user who last edited this permission.
     */
    public function userEdit()
    {
        return $this->belongsTo(User::class, 'user_edit');
    }

    /**
     * Get the user who deleted this permission.
     */
    public function userDeleted()
    {
        return $this->belongsTo(User::class, 'user_deleted');
    }
} 