<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\Sku;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PriceResolverService
{
    /**
     * Determina el precio unitario ganador aplicando escalas por volumen y prioridades.
     */
    public function resolveWinningPrice(Sku $sku, int $quantity, string $branchId, ?Carbon $now = null): object
    {
        $now = $now ?? now();

        // 1. Intento de resolución en la sucursal activa del contexto
        $price = $this->queryPriceTable($sku->id, $branchId, $quantity, $now);

        if ($price) {
            return $price;
        }

        // 2. FALLBACK ESCENARIO 2: Búsqueda en la sucursal predeterminada (is_default = 1)
        $defaultBranchId = Branch::where('is_default', true)->where('is_active', true)->value('id');

        if ($defaultBranchId && $defaultBranchId !== $branchId) {
            $fallbackPrice = $this->queryPriceTable($sku->id, (string) $defaultBranchId, $quantity, $now);
            if ($fallbackPrice) {
                return $fallbackPrice;
            }
        }

        // 3. SEGUNDO FALLBACK: Retorno estructurado en cero para forzar bloqueo de visualización/venta
        return (object) [
            'list_price'  => 0.00,
            'final_price' => 0.00,
            'type'        => 'NONE',
            'is_fallback' => true
        ];
    }

    /**
     * Ejecuta la consulta estructurada optimizada sobre la matriz de precios.
     */
    private function queryPriceTable(string $skuId, string $branchId, int $quantity, Carbon $now): ?object
    {
        return DB::table('prices')
            ->where('sku_id', $skuId)
            ->where('branch_id', $branchId)
            ->where('deleted_epoch', 0)
            ->where('min_quantity', '<=', $quantity) // Evalúa escala de volumen
            ->where('valid_from', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            })
            ->orderBy('priority', 'desc')     // Mayor nivel de prioridad gana
            ->orderBy('min_quantity', 'desc') // Escala más alta y específica gana por volumen
            ->select('list_price', 'final_price', 'type')
            ->first();
    }
}