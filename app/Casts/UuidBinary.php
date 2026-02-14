<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class UuidBinary implements CastsAttributes
{
    /**
     * Transformar BINARIO (DB) -> HEXADECIMAL (PHP/JSON)
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }
        
        // Si ya es texto legible, lo devolvemos
        if (ctype_print($value)) {
            return $value;
        }

        // Convertimos binario a hex
        return bin2hex($value);
    }

    /**
     * Transformar HEXADECIMAL (PHP) -> BINARIO (DB)
     * Esto arregla el Login porque convierte el ID antes de buscarlo.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        // Si es un string hexadecimal de 32 caracteres, lo hacemos binario
        if (is_string($value) && ctype_xdigit($value) && strlen($value) === 32) {
            return hex2bin($value);
        }

        return $value; // Asumimos que ya es binario
    }
}