<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasUv7;

    protected $guard_name = 'super_admin';

    protected $fillable = [
        'first_name', 
        'last_name', 
        'phone', 
        'email', 
        'password', 
        'branch_id', 
        'is_active'
    ];

    protected $hidden = [
        'password', 
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string 
    { 
        return "{$this->first_name} {$this->last_name}"; 
    }

    public function branch(): BelongsTo 
    { 
        return $this->belongsTo(Branch::class); 
    }
}