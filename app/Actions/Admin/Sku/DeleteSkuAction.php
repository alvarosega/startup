<?php

declare(strict_types=1);

namespace App\Actions\Admin\Sku;

use App\Models\Sku;
use Illuminate\Validation\ValidationException;

class DeleteSkuAction
{
    public function execute(Sku $sku): void
    {
        $hasStock = $sku->inventoryLots()->where('current_stock', '>', 0)->exists();

        if ($hasStock) {
            throw ValidationException::withMessages([
                'sku' => "BLOQUEO_SEGURIDAD: La variante comercial '{$sku->code}' posee existencias físicas reales."
            ]);
        }

        $sku->delete();
    }
}