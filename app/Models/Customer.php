<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasUv7;

    protected $guard_name = 'customer';

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
     * Sanitización de Identidad: Normaliza el Country Code a Mayúsculas.
     */
    protected function countryCode(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper($value),
        );
    }

    public function profile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class, 'customer_id', 'id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id', 'id');
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites', 'customer_id', 'product_id')
                    ->withTimestamps();
    }
}