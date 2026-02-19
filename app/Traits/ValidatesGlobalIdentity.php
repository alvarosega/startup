<?php

namespace App\Traits;

use Illuminate\Validation\Rule;

trait ValidatesGlobalIdentity
{
    /**
     * Limpia el teléfono para que sea un string internacional puro (+XXXXXXXX)
     */
    protected function normalizeIdentityData()
    {
        if ($this->has('phone') && !empty($this->phone)) {
            $phone = $this->phone;
            
            // Eliminamos espacios, guiones y aseguramos el signo +
            $cleanPhone = preg_replace('/[^\+0-9]/', '', $phone);
            
            if (!str_starts_with($cleanPhone, '+')) {
                $cleanPhone = '+' . $cleanPhone;
            }

            $this->merge(['phone' => $cleanPhone]);
        }
    }

    /**
     * Reglas para validación de teléfono en los 3 silos
     */
    protected function globalPhoneRules()
    {
        return ['required', 'string', 'min:8', 'max:20'];
    }

    /**
     * Reglas para validación de email en los 3 silos
     */
    protected function globalEmailRules()
    {
        return ['required', 'email', 'max:255'];
    }
}