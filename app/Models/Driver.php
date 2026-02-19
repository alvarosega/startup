<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Driver extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasUuids, SoftDeletes;

    protected $guard_name = 'driver';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'phone', 'email', 'password', 'status', 'current_lat', 'current_lng'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'current_lat' => 'float',
        'current_lng' => 'float',
    ];


    public function details() 
    { 
        return $this->hasOne(DriverDetail::class, 'driver_id', 'id'); 
    }
    
}