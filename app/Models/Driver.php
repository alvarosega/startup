<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasUuids, SoftDeletes;

    protected $guard_name = 'driver';
    public $incrementing = false;
    protected $keyType = 'string';
    
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

    public function details(): HasOne 
    { 
        return $this->hasOne(DriverDetail::class, 'driver_id', 'id'); 
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
    public function locationLogs(): HasMany
    {
        return $this->hasMany(DriverLocationLog::class, 'driver_id', 'id');
    }
    public function profile(): HasOne
    {
        // Forzamos la llave foránea para que coincida con la migración
        return $this->hasOne(DriverProfile::class, 'driver_id', 'id');
    }
}