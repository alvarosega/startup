<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    protected $guard_name = 'customer';

    // --- CONFIGURACIÓN BINARIA ---
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

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
        'id', 
        'branch_id'
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    // --- 1. BOOT: Generar ID Binario al crear ---
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                // Generar UUID v4 y convertirlo a 16 bytes binarios
                $model->id = hex2bin(str_replace('-', '', (string) Str::uuid()));
            }
        });
    }

    // --- 2. ELIMINAMOS getIdAttribute y setIdAttribute ---
    // Al borrarlos, $user->id devolverá el BINARIO puro. 
    // Esto arregla la relación con Profile y Address.

    // --- 3. SERIALIZACIÓN: Binario -> Hex (Para Vue/JSON) ---
    // Este método se ejecuta automáticamente cuando Inertia o Laravel convierten el modelo a JSON.
    public function toArray()
    {
        $array = parent::toArray();
        
        // Inyectamos las versiones Hexadecimales para que Vue las entienda
        $rawId = $this->getRawOriginal('id');
        $array['id'] = $rawId ? bin2hex($rawId) : null;
        
        $rawBranch = $this->getRawOriginal('branch_id');
        if ($rawBranch) {
            $array['branch_id'] = bin2hex($rawBranch);
        }
        
        return $array;
    }

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

    // Accessor auxiliar por si necesitas el Hex manualmente en PHP
    public function getIdHexAttribute()
    {
        return bin2hex($this->getRawOriginal('id'));
    }
}