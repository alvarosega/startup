<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Sku;

use App\Models\Catalog\Sku;
use Illuminate\Validation\ValidationException;

class DeleteSkuAction
{
    public function execute(Sku $sku): void
    {
        $hasStock = $sku->inventoryLots()->where('current_stock', '>', 0)->exists();

        if ($hasStock) {
            throw ValidationException::withMessages([
                'sku' => "BLOQUEO_SEGURIDAD: La variante comercial '{$sku->code}' posee existencias físicas reales en lotes de inventario."
            ]);
        }

        $sku->delete();
    }
}