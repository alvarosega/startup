<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

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

    protected $appends = ['completion_percentage', 'avatar_url'];

    public function getAvatarUrlAttribute()
    {
        if (empty($this->avatar_source)) {
            return asset("assets/avatars/avatar_1.svg");
        }
    
        if ($this->avatar_type === 'icon') {
            return asset("assets/avatars/{$this->avatar_source}");
        }
    
        return route('avatar.download', ['filename' => basename($this->avatar_source)]);
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
}