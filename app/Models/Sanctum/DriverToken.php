<?php

namespace App\Models\Sanctum;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class DriverToken extends SanctumPersonalAccessToken
{
    protected $table = 'driver_tokens';

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'driver_id',
    ];

    public function setDriverIdAttribute($value)
    {
        if (is_string($value) && strlen($value) === 32) {
            $this->attributes['driver_id'] = hex2bin($value);
        } else {
            $this->attributes['driver_id'] = $value;
        }
    }
}