<?php

declare(strict_types=1);

namespace App\Models\Users;

use App\Models\Operations\Branch;
use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

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
        'is_active',
        'last_login_at',
        'last_seen_at',
    ];

    protected $hidden = [
        'password', 
        'mfa_secret',
    ];

    protected $casts = [
        'password'      => 'hashed',
        'is_active'     => 'boolean',
        'last_seen_at'  => 'datetime',
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

    public function accessLogs(): HasMany
    {
        return $this->hasMany(AdminAccessLog::class);
    }
}