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
        'is_active',
        'user_add',
        'user_edit',
        'user_deleted',
        'pms_restaurant_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'user_add' => 'integer',
        'user_edit' => 'integer',
        'user_deleted' => 'integer',
        'pms_restaurant_id' => 'integer',
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

    /**
     * Scope a query to only include role categories for a specific restaurant.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|string $restaurantId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForRestaurant($query, $restaurantId)
    {
        return $query->where('pms_restaurant_id', $restaurantId);
    }

    /**
     * Scope a query to only include global role categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('pms_restaurant_id');
    }
}