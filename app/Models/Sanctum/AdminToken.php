<?php

namespace App\Models\Sanctum;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class AdminToken extends SanctumPersonalAccessToken
{
    protected $table = 'admin_tokens';

    // Sobrescribimos fillable porque no usamos 'tokenable_type'
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'admin_id', // FK Específica
    ];

    // Mutator: Asegurar que si entra un ID en Hex (String), se guarde en Binario
    public function setAdminIdAttribute($value)
    {
        if (is_string($value) && strlen($value) === 32) {
            $this->attributes['admin_id'] = hex2bin($value);
        } else {
            $this->attributes['admin_id'] = $value;
        }
    }
}