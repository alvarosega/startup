<?php

namespace App\Services\Finance;

use App\Models\Sku;
use Carbon\Carbon;

class PriceResolverService
{
    /**
     * Resuelve el precio ganador usando la colección ya cargada en el modelo.
     */
    public function resolveWinningPrice(Sku $sku, int $quantity, Carbon $now): object
    {
        // 1. OBTENCIÓN DEL PRECIO GANADOR EN MEMORIA (In-Memory Filtering)
        $winningPrice = $sku->prices
            ->filter(fn($p) => 
                $p->min_quantity <= $quantity &&
                $p->valid_from <= $now &&
                (is_null($p->valid_to) || $p->valid_to >= $now)
            )
            ->sortByDesc('priority')
            ->sortByDesc('min_quantity')
            ->first();

        // Fallback: Contrato estricto si no hay reglas
        if (!$winningPrice) {
            $winningPrice = (object) [
                'final_price' => $sku->base_price,
                'list_price'  => $sku->base_price,
                'type'        => 'regular'
            ];
        }

        // 2. DETECCIÓN DE UPSELL (Próximo mejor descuento)
        $nextTier = $sku->prices
            ->filter(fn($p) => 
                $p->min_quantity > $quantity &&
                $p->final_price < $winningPrice->final_price &&
                $p->valid_from <= $now &&
                (is_null($p->valid_to) || $p->valid_to >= $now)
            )
            ->sortBy('min_quantity')
            ->first();

        return (object) [
            'final_price' => (float) $winningPrice->final_price,
            'list_price'  => (float) $winningPrice->list_price,
            'type'        => $winningPrice->type,
            'next_tier'   => $nextTier ? [
                'min_quantity' => (int) $nextTier->min_quantity,
                'final_price'  => (float) $nextTier->final_price
            ] : null
        ];
    }
}