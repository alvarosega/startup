<?php

declare(strict_types=1);

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CustomerToken extends Model
{
    protected $table = 'password_reset_codes';
    
    // Desactivación de incrementos y asignación manual
    protected $primaryKey = ['email', 'token'];
    public $incrementing = false;
    protected $keyType = 'string';
    
    public $timestamps = false; // Manejado mediante columnas explícitas en la migración

    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Sobrescribe el comportamiento del ORM para soportar búsquedas sobre llaves compuestas.
     */
    protected function setKeysForSaveQuery($query): Builder
    {
        foreach ((array) $this->primaryKey as $keyName) {
            $query->where($keyName, '=', $this->getAttribute($keyName));
        }

        return $query;
    }
}