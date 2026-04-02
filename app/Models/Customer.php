<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasUuids;

    protected $guard_name = 'customer';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var array<int, string>
     * EXCLUSIÓN: 'id' y 'trust_score' (Admin Only).
     */
    protected $fillable = [
        'branch_id',
        'phone',
        'country_code',
        'email',
        'password',
        'is_active',
        'idempotency_key',
        'last_login_at',
        'latitude',
        'longitude',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'idempotency_key',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Fuerza el uso de UUIDv7 para ordenamiento por tiempo nativo.
     */
    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    /**
     * Sanitización de Identidad: Normaliza el Country Code a Mayúsculas.
     */
    protected function countryCode(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper($value),
        );
    }

    // --- RELACIONES DE ALTA DENSIDAD ---

    public function profile()
    {
        return $this->hasOne(CustomerProfile::class, 'customer_id', 'id');
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id', 'id');
    }
    public function favorites(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        // Relación directa a nivel Producto (Ley de Identidad)
        return $this->belongsToMany(Product::class, 'favorites', 'customer_id', 'product_id')
                    ->withTimestamps();
    }
}