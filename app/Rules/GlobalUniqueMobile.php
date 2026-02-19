<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class GlobalUniqueMobile implements ValidationRule
{
    public function __construct(protected $ignoreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tables = ['admins', 'customers', 'drivers'];
        
        foreach ($tables as $table) {
            $query = DB::table($table)->where($attribute, $value);
            
            if ($this->ignoreId) {
                // CORRECCIÓN CRÍTICA:
                // Ya no somos binarios. Tratamos el ID como texto simple.
                $query->where('id', '!=', (string) $this->ignoreId);
            }

            if ($query->exists()) {
                $fail("Este " . ($attribute === 'email' ? 'correo' : 'teléfono') . " ya está registrado en el sistema.");
                return;
            }
        }
    }
}