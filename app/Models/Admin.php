<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasUuids;

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
    ];

    public function getFullNameAttribute() { 
        return "{$this->first_name} {$this->last_name}"; 
    }

    public function branch() { 
        return $this->belongsTo(Branch::class); 
    }
}
