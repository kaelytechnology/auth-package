<?php

namespace Kaely\AuthPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'address',
        'birth_date',
        'gender',
        'user_add',
        'user_edit',
        'user_deleted'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'birth_date' => 'date',
        'user_add' => 'integer',
        'user_edit' => 'integer',
        'user_deleted' => 'integer',
    ];

    /**
     * Get the user that owns the person.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created this person.
     */
    public function userAdd()
    {
        return $this->belongsTo(User::class, 'user_add');
    }

    /**
     * Get the user who last edited this person.
     */
    public function userEdit()
    {
        return $this->belongsTo(User::class, 'user_edit');
    }

    /**
     * Get the user who deleted this person.
     */
    public function userDeleted()
    {
        return $this->belongsTo(User::class, 'user_deleted');
    }

    /**
     * Get the full name of the person.
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the age of the person.
     */
    public function getAgeAttribute()
    {
        if (!$this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }
} 