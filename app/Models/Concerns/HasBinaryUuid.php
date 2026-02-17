<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasBinaryUuid
{
    public function initializeHasBinaryUuid()
    {
        $this->incrementing = false;
        $this->keyType = 'string';
    }

    public static function bootHasBinaryUuid()
    {
        static::creating(function (Model $model) {
            $keyName = $model->getKeyName();
            if (empty($model->{$keyName})) {
                $model->attributes[$keyName] = hex2bin(str_replace('-', '', (string) Str::uuid()));
            }
        });
    }

    // Asegura que al pedir el ID siempre venga en Hex
    public function getKey()
    {
        $key = $this->getRawOriginal($this->getKeyName());
        return (is_string($key) && strlen($key) === 16) ? bin2hex($key) : $key;
    }

    // BLINDAJE TOTAL: Convierte todo lo binario a Hex antes de que se serialice a JSON
    public function toArray()
    {
        $attributes = parent::toArray();
        foreach ($attributes as $key => $value) {
            $raw = $this->getRawOriginal($key);
            if (is_string($raw) && strlen($raw) === 16 && !ctype_print($raw)) {
                $attributes[$key] = bin2hex($raw);
            }
        }
        return $attributes;
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_array($column)) {
                    return parent::where($column, $operator, $value, $boolean);
                }
                if (func_num_args() === 2) { $value = $operator; $operator = '='; }

                // Conversión automática Hex -> Bin para consultas
                if (is_string($value) && strlen($value) === 32 && ctype_xdigit($value)) {
                    if (str_ends_with($column, 'id') || $column === $this->model->getKeyName()) {
                        $value = hex2bin($value);
                    }
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }
}