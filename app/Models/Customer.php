<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasUuids;

    protected $guard_name = 'customer';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 
        'branch_id', // FK Binaria
        'phone', 
        'country_code', 
        'email', 
        'password', 
        'trust_score', 
        'is_active',
        'last_login_at'
    ];

    // ASEGURAR que branch_id esté en hidden (Ya lo tienes, pero verifícalo)
    protected $hidden = [
        'password', 
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    // --- 4. RELACIONES ---
    public function profile()
    {
        // Especificamos claves para mayor seguridad
        return $this->hasOne(CustomerProfile::class, 'customer_id', 'id');
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id', 'id');
    }


}