<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUuidv7
{
    protected static function bootHasUuidv7(): void
    {
        static::creating(function (Model $model) {
            if (!$model->getKey()) {
                // CORRECCIÓN: orderedUuid es más eficiente para índices SQL que uuid() normal
                $model->{$model->getKeyName()} = (string) Str::orderedUuid();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}