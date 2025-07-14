<?php

namespace Kaely\AuthPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'user_add',
        'user_edit',
        'user_deleted'
    ];

    protected $casts = [
        'user_add' => 'integer',
        'user_edit' => 'integer',
        'user_deleted' => 'integer',
    ];

    /**
     * Get the roles that belong to this category.
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Get the user who created this role category.
     */
    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_add');
    }

    /**
     * Get the user who last edited this role category.
     */
    public function userEdit()
    {
        return $this->belongsTo(User::class, 'user_edit');
    }

    /**
     * Get the user who deleted this role category.
     */
    public function userDeleted()
    {
        return $this->belongsTo(User::class, 'user_deleted');
    }
} 