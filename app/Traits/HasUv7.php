<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUv7
{
    /**
     * Inicializa el trait dinámicamente forzando las propiedades del modelo.
     */
    public function initializeHasUv7(): void
    {
        $this->keyType = 'string';
        $this->incrementing = false;
    }

    /**
     * Genera el identificador UUIDv7 en el ciclo de creación.
     */
    protected static function bootHasUv7(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid7();
            }
        });
    }
}