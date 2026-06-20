<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasUv7, SoftDeletes;

    protected $guard_name = 'driver';
    
    protected $fillable = [
        'branch_id',
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
    
    public function profile(): HasOne
    {
        return $this->hasOne(DriverProfile::class, 'driver_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function locationLogs(): HasMany
    {
        return $this->hasMany(DriverLocationLog::class, 'driver_id', 'id');
    }
}