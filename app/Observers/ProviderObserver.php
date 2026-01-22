<?php

namespace App\Observers;

use App\Models\Provider;
use Illuminate\Support\Str;

class ProviderObserver
{
    public function creating(Provider $provider): void
    {
        $this->sanitize($provider);
    }

    public function updating(Provider $provider): void
    {
        $this->sanitize($provider);
    }

    /**
     * Estandarización de datos antes de guardar
     */
    private function sanitize(Provider $provider): void
    {
        // Forzar Mayúsculas para búsquedas consistentes
        $provider->company_name = Str::upper($provider->company_name);
        
        if ($provider->commercial_name) {
            $provider->commercial_name = Str::upper($provider->commercial_name);
        }

        // Limpiar NIT (Quitar espacios laterales)
        $provider->tax_id = trim($provider->tax_id);
    }
    
    // Si borramos un proveedor, ¿qué pasa? 
    // Por ahora SoftDeletes maneja la seguridad, no requerimos acción destructiva extra.
}