<?php

namespace App\Traits;

use App\Rules\GlobalUniqueMobile;

trait ValidatesGlobalIdentity
{
    /**
     * Reglas de email únicas en los 3 silos.
     */
    protected function globalEmailRules($ignoreId = null): array
    {
        return ['required', 'string', 'email', 'max:255', new GlobalUniqueMobile($ignoreId)];
    }

    /**
     * Reglas de teléfono únicas en los 3 silos.
     */
    protected function globalPhoneRules($ignoreId = null): array
    {
        return ['required', 'string', new GlobalUniqueMobile($ignoreId)];
    }

    /**
     * ESTA ES LA FUNCIÓN QUE FALTA:
     * Normaliza los datos antes de que pasen por la validación.
     */
    protected function normalizeIdentityData()
    {
        // Limpiamos espacios en el teléfono para evitar fallos de duplicidad visual
        if ($this->has('phone') && $this->phone) {
            $this->merge([
                'phone' => str_replace([' ', '-', '(', ')'], '', $this->phone)
            ]);
        }
        
        // Forzamos el email a minúsculas
        if ($this->has('email') && $this->email) {
            $this->merge([
                'email' => strtolower(trim($this->email))
            ]);
        }
    }
}