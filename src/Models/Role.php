<?php

namespace Kaely\AuthPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'role_category_id',
        'user_add',
        'user_edit',
        'user_deleted',
        'status'
    ];

    protected $casts = [
        'user_add' => 'integer',
        'user_edit' => 'integer',
        'user_deleted' => 'integer',
    ];

    /**
     * Get the role category that owns the role.
     */
    public function roleCategory()
    {
        return $this->belongsTo(RoleCategory::class);
    }

    /**
     * Get the users that belong to this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    /**
     * Get the permissions that belong to this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Get the user who created this role.
     */
    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_add');
    }

    /**
     * Get the user who last edited this role.
     */
    public function userEdit()
    {
        return $this->belongsTo(User::class, 'user_edit');
    }

    /**
     * Get the user who deleted this role.
     */
    public function userDeleted()
    {
        return $this->belongsTo(User::class, 'user_deleted');
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions->contains('slug', $permission);
        }

        return !!$permission->intersect($this->permissions)->count();
    }

    /**
     * Assign permissions to role.
     */
    public function assignPermissions($permissions)
    {
        if (is_array($permissions)) {
            $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id');
            $this->permissions()->sync($permissionIds);
        } else {
            $this->permissions()->sync($permissions);
        }
    }
}