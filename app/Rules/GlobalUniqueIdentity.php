<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class GlobalUniqueIdentity implements ValidationRule
{
    public function __construct(protected $ignoreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tables = ['admins', 'customers', 'drivers'];
        
        foreach ($tables as $table) {
            $query = DB::table($table)->where($attribute, $value);
            
            // CORRECCIÓN: Solo 'drivers' maneja SoftDeletes nativo (deleted_at).
            // 'customers' y 'admins' no tienen borrado lógico en sus estructuras.
            if ($table === 'drivers') {
                $query->whereNull('deleted_at');
            }

            if ($this->ignoreId) {
                $query->where('id', '!=', (string) $this->ignoreId);
            }

            if ($query->exists()) {
                $fail("Este " . ($attribute === 'email' ? 'correo' : 'teléfono') . " ya está registrado en el sistema.");
                return;
            }
        }
    }
}