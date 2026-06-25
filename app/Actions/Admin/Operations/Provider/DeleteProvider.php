<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Provider;

use App\Models\Operations\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteProvider
{
    /**
     * Aplica restricciones operativas atómicas sin purgar ni alterar marcas comerciales huérfanas.
     */
    public function execute(Provider $provider): bool
    {
        return DB::transaction(function () use ($provider) {
            if ($provider->brands()->exists()) {
                throw ValidationException::withMessages([
                    'provider' => 'PROTECCIÓN_ACTIVA: Operación cancelada. El proveedor tiene marcas comerciales vinculadas en el catálogo.'
                ]);
            }

            return (bool) $provider->delete();
        });
    }
}