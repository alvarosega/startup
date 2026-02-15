<?php

namespace App\Traits;

trait ValidatesGlobalIdentity
{
    /**
     * Reglas de unicidad para Email en todos los silos.
     */
    protected function globalEmailRules($ignoreId = null, $table = null): array
    {
        return [
            'required', 'email', 'max:255',
            "unique:admins,email,{$ignoreId},id",
            "unique:customers,email,{$ignoreId},id",
            "unique:drivers,email,{$ignoreId},id",
        ];
    }

    /**
     * Reglas de unicidad para Teléfono en todos los silos.
     */
    protected function globalPhoneRules($ignoreId = null, $table = null): array
    {
        return [
            'required', 'string',
            "unique:admins,phone,{$ignoreId},id",
            "unique:customers,phone,{$ignoreId},id",
            "unique:drivers,phone,{$ignoreId},id",
        ];
    }
}