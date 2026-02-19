<?php

namespace App\Actions\Admin\Provider;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class DeleteProvider
{
    /**
     * Ejecuta el Soft Delete del proveedor bajo una transacción.
     */
    public function execute(Provider $provider): bool
    {
        return DB::transaction(fn() => $provider->delete());
    }
}