<?php

namespace App\Traits;

use App\Rules\GlobalUniqueMobile; // <--- OBLIGATORIO

trait ValidatesGlobalIdentity
{
    /**
     * Limpia el teléfono para que sea un string internacional puro (+XXXXXXXX)
     */
    protected function normalizeIdentityData()
    {
        if ($this->has('phone') && !empty($this->phone)) {
            $phone = $this->phone;
            $cleanPhone = preg_replace('/[^\+0-9]/', '', $phone);
            
            if (!str_starts_with($cleanPhone, '+')) {
                $cleanPhone = '+' . $cleanPhone;
            }

            $this->merge(['phone' => $cleanPhone]);
        }
    }

    /**
     * Reglas para validación de teléfono en los 3 silos
     * ACEPTA $ignoreId para procesos de edición (UPDATE)
     */
    protected function globalPhoneRules($ignoreId = null) // <--- CORRECCIÓN: Declarar parámetro
    {
        return [
            'required', 
            'string', 
            'min:8', 
            'max:20', 
            new GlobalUniqueMobile($ignoreId) // <--- Ahora la variable sí existe aquí
        ];
    }

    /**
     * Reglas para validación de email en los 3 silos
     */
    protected function globalEmailRules($ignoreId = null) // <--- CORRECCIÓN: Declarar parámetro
    {
        return [
            'required', 
            'email', 
            'max:255', 
            new GlobalUniqueMobile($ignoreId)
        ];
    }
}