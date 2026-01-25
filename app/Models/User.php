<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use App\Models\Concerns\HasPrefixedId;
use App\Models\Concerns\HasUuidv7; // Nuestro Trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    use HasUuidv7;
    use HasPrefixedId;

    // Definimos el prefijo para este modelo
    protected $prefix = 'usr';

    protected $fillable = [
        // Mantenemos solo lo esencial para creación masiva si fuera necesaria.
        // En Actions usaremos asignación directa para mayor seguridad.
        'phone',
        'country_code',
        'email',
        'password',
        'branch_id',
        'trust_score',
        'is_active',
        'current_level_id',
        'avatar_type',
        'avatar_source',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'id', // Ocultamos el ID puro UUID, mostramos solo el prefijado via Trait
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // --- RELACIONES ---



    public function socialIdentities()
    {
        return $this->hasMany(SocialIdentity::class);
    }
    


    public function billingInfos()
    {
        return $this->hasMany(UserBillingInfo::class);
    }
    // 1. Relación con Perfil (Ya la tenías, pero verifica)
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    // 2. Relación con Sucursal (Necesaria para el Header del Dashboard)
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // 3. Relación con Verificaciones (La que causó el error 500)
    public function verifications()
    {
        return $this->hasMany(UserVerification::class);
    }

    // 4. Relación con Direcciones (También se usa en Inertia Shared Data)
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    // --- ACCESSORS (Si usabas completion_percentage) ---
    // Si tu middleware usa $user->completion_percentage, necesitas esto o quitarlo del middleware
    public function getCompletionPercentageAttribute(): int
    {
        // Lógica simple para evitar errores
        return $this->profile ? 100 : 50; 
    }
    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }
}