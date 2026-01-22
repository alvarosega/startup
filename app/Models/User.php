<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
// Al inicio del archivo importa:
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles; 

    protected $fillable = [
        'branch_id',
        'phone',
        'country_code',
        'email',
        'password',
        'trust_score',
        'is_active',
        'current_level_id',
        'last_login_at',
        'avatar_type',
        'avatar_source',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // --- RELACIONES ---

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    public function socialIdentities()
    {
        return $this->hasMany(SocialIdentity::class);
    }

    public function verifications()
    {
        return $this->hasMany(UserVerification::class);
    }

    // --- ACCESORS & HELPERS ---

    public function getAvatarInitialsAttribute()
    {
        if ($this->profile && $this->profile->first_name) {
            return strtoupper(substr($this->profile->first_name, 0, 1));
        }
        return '?';
    }



    // Dentro de la clase User:
    protected $appends = ['avatar_url', 'completion_percentage']; // Asegúrate que esté aquí

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar_type === 'custom' && $this->avatar_source) {
            // Esta ruta debe existir en web.php para servir archivos privados
            return route('avatar.download', ['filename' => basename($this->avatar_source)]);
        }
        
        // Si es ícono o null
        $icon = $this->avatar_source ?? 'avatar_1.svg';
        // Asegúrate de que la ruta coincida con donde tienes tus SVGs en public/
        return asset('assets/avatars/' . $icon); 
    }

    public function getCompletionPercentageAttribute(): int
    {
        $p = 0;
        $profile = $this->profile;
    
        if ($profile?->first_name && $profile?->last_name && $profile?->birth_date) {
            $p = 40;
        }
        if ($this->email) {
            $p += 30;
        }
        if ($profile?->is_identity_verified) {
            $p += 30;
        }
        
        return $p;
    }

    // Helper simplificado: Solo devuelve su sucursal actual
    public function getAllowedBranchIds()
    {
        return $this->branch_id ? [$this->branch_id] : [];
    }
    public function getNameAttribute(): string
    {
        if ($this->relationLoaded('profile') && $this->profile) {
            return trim("{$this->profile->first_name} {$this->profile->last_name}");
        }
        
        // Si no tiene perfil, devolvemos el email o el teléfono
        return $this->email ?? $this->phone ?? 'Usuario';
    }


    // Relación: Un usuario tiene muchas direcciones (Historial)
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    // Helper: Obtener la dirección predeterminada para el Checkout
    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    // Relación: Datos de facturación (NITs)
    public function billingInfos()
    {
        return $this->hasMany(UserBillingInfo::class);
    }
}