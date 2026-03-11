<?php

namespace App\Services\Finance;

use App\Models\Sku;
use App\Models\Price;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PriceResolverService
{
    /**
     * Resuelve el precio ganador y detecta el próximo escalón de descuento.
     */
    public function resolveWinningPrice(Sku $sku, string $branchId, int $quantity): object
    {
        $now = Carbon::now();

        // 1. OBTENCIÓN DEL PRECIO GANADOR (Winning Price)
        // Aplicamos el índice idx_price_winning_lookup
        $winningPrice = Price::where('sku_id', $sku->id)
            ->where('branch_id', $branchId)
            ->where('min_quantity', '<=', $quantity)
            ->where('valid_from', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            })
            ->orderBy('priority', 'desc')     // Prioridad de negocio (Staff > Wholesale > Regular)
            ->orderBy('min_quantity', 'desc') // Mayor volumen alcanzado
            ->orderBy('created_at', 'desc')   // El más reciente en caso de empate
            ->first();

        // Fallback: Si no hay regla de precio, usamos el base_price del SKU como 'regular'
        if (!$winningPrice) {
            $winningPrice = (object) [
                'final_price' => $sku->base_price,
                'list_price'  => $sku->base_price,
                'type'        => 'regular',
                'priority'    => 1
            ];
        }

        // 2. DETECCIÓN DE UPSELL (Next Better Tier)
        // Buscamos si existe un precio con mejor final_price para una cantidad mayor
        $nextTier = Price::where('sku_id', $sku->id)
            ->where('branch_id', $branchId)
            ->where('min_quantity', '>', $quantity)
            ->where('final_price', '<', $winningPrice->final_price)
            ->where('valid_from', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            })
            ->orderBy('min_quantity', 'asc') // El escalón más cercano
            ->first();

        return (object) [
            'final_price' => $winningPrice->final_price,
            'list_price'  => $winningPrice->list_price,
            'type'        => $winningPrice->type,
            'next_tier'   => $nextTier
        ];
    }
}
