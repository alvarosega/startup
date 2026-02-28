<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasUuids, SoftDeletes;

    protected $guard_name = 'driver';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'phone', 
        'email', 
        'password', 
        'status', 
        'is_online', 
        'is_available'
    ];
    
    protected $hidden = [
        'password', 
        'remember_token'
    ];
    
    protected $casts = [
        'password' => 'hashed',
        'is_online' => 'boolean',
        'is_available' => 'boolean',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function details(): HasOne 
    { 
        return $this->hasOne(DriverDetail::class, 'driver_id', 'id'); 
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_driver', 'driver_id', 'branch_id')->withTimestamps();
    }

    public function locationLogs(): HasMany
    {
        return $this->hasMany(DriverLocationLog::class, 'driver_id', 'id');
    }
}