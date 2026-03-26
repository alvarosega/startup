<?php

declare(strict_types=1);

namespace App\Actions\Customer\RetailMedia;

use App\Models\{AdCreative, Sku, Bundle};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetActiveAdCreativesAction
{
    /**
     * Resuelve creativos publicitarios validados por sucursal y ubicación.
     * Soporta anclaje opcional a un bundle específico.
     */
    public function execute(string $branchId, string $placementCode, ?string $bundleId = null): Collection
    {
        // La llave de caché discrimina si es un banner global o de un pack específico
        $scope = $bundleId ?? 'global';
        $cacheKey = "resolved_ads_v8_{$placementCode}_{$branchId}_{$scope}";

        return Cache::remember($cacheKey, 600, function () use ($branchId, $placementCode, $bundleId) {
            $query = AdCreative::query()
                ->where('is_active', true)
                ->where('branch_id', $branchId);

            // FILTRO DE ANCLAJE: Si hay bundleId, traemos sus banners. Si no, banners globales.
            if ($bundleId) {
                $query->where('bundle_id', $bundleId);
            } else {
                $query->whereNull('bundle_id');
            }

            // Ubicación (Placement) y Campaña
            $query->whereHas('placement', fn($q) => $q->where('code', $placementCode))
                  ->whereHas('campaign', function ($q) {
                      $q->where('is_active', true)
                        ->where(fn($sub) => $sub->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                        ->where(fn($sub) => $sub->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
                  });

            // Validación de Target (Polimorfismo)
            $query->where(function ($query) use ($branchId) {
                $query->whereHasMorph('target', [Sku::class], function ($q) use ($branchId) {
                    $q->whereHas('prices', fn($p) => $p->where('branch_id', $branchId)->where('final_price', '>', 0));
                })
                ->orWhereHasMorph('target', [Bundle::class], function ($q) {
                    $q->active(); // Scope definido en el modelo Bundle
                });
            });

            return $query->with(['target'])->orderBy('sort_order', 'asc')->get();
        });
    }
}