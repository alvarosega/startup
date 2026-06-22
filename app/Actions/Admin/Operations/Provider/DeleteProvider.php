<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Provider;

use App\Models\Operations\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class DeleteProvider
{
    public function execute(Provider $provider): bool
    {
        $deleted = DB::transaction(function () use ($provider) {
            if ($provider->brands()->exists()) {
                throw ValidationException::withMessages([
                    'provider' => 'PROTECCIÓN_ACTIVA: Operación cancelada. El proveedor tiene marcas comerciales vinculadas en el catálogo.'
                ]);
            }

            return (bool) $provider->delete();
        });

        if ($deleted) {
            Cache::increment('admin_providers_version');
        }

        return $deleted;
    }
}