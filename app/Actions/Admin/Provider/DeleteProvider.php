<?php

namespace App\Actions\Admin\Provider;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use App\DTOs\Admin\Provider\ProviderData;
use Illuminate\Support\Facades\Cache;

class DeleteProvider {
    public function execute(Provider $provider): bool {
        return DB::transaction(function () use ($provider) {
            // REGLA 2.B: Verificación Zero-Trust (Evitar huérfanos)
            if ($provider->brands()->exists()) {
                throw new \Exception("PROTECCIÓN_ACTIVA: El proveedor tiene marcas vinculadas.");
            }

            Cache::increment('admin_providers_version');
            return $provider->delete();
        });
    }
}