<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Sku;

use App\Models\Catalog\Sku;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteSkuAction
{
    /**
     * Aplica la exclusión lógica de variantes comerciales aislando consultas crudas de inventarios y precios.
     */
    public function execute(Sku $sku): void
    {
        // RECTIFICACIÓN: Ejecución vía Query Builder crudo para eludir quiebres por namespaces sobre modelos ausentes
        $hasStock = DB::table('inventory_lots')
            ->where('sku_id', $sku->id)
            ->where('quantity', '>', 0)
            ->exists();

        if ($hasStock) {
            throw ValidationException::withMessages([
                'sku' => "BLOQUEO_SEGURIDAD: La variante comercial '{$sku->code}' posee existencias físicas reales en lotes de inventario."
            ]);
        }

        // RECTIFICACIÓN: Evaluación perimetral del silo comercial para impedir la remoción de SKUs con precios asignados
        $hasPrices = DB::table('prices')
            ->where('sku_id', $sku->id)
            ->where('deleted_epoch', 0)
            ->exists();

        if ($hasPrices) {
            throw ValidationException::withMessages([
                'sku' => "BLOQUEO_SEGURIDAD: Operación cancelada. La variante comercial posee estructuras de precio activas vinculadas."
            ]);
        }

        $sku->delete();
    }
}