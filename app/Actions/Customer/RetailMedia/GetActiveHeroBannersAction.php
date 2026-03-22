<?php

namespace App\Actions\Customer\RetailMedia;

use App\Models\AdCreative;
use App\Models\Sku;
use App\Models\Bundle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetActiveHeroBannersAction
{
    public function execute(string $branchId): Collection
    {
        // Cambiamos la versión de la caché para forzar la actualización
        $cacheKey = "resolved_hero_banners_v4_{$branchId}";

        return Cache::remember($cacheKey, 600, function () use ($branchId) {
            return AdCreative::query()
                ->where('is_active', true)
                // 1. ANCLAJE DIRECTO: Filtro simple por columna
                ->where('branch_id', $branchId) 
                
                // 2. Ubicación: Home Hero
                ->whereHas('placement', fn($q) => $q->where('code', 'HOME_HERO'))
                
                // 3. Campaña: Activa
                ->whereHas('campaign', function ($q) {
                    $q->where('is_active', true)
                        ->where(fn($sub) => $sub->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                        ->where(fn($sub) => $sub->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
                })
                
                // 4. Filtro Polimórfico (Target)
                ->where(function ($query) use ($branchId) {
                    $query->whereHasMorph('target', [Sku::class], function ($q) use ($branchId) {
                        $q->whereHas('prices', fn($p) => 
                            $p->where('branch_id', $branchId)->where('final_price', '>', 0)
                        );
                    })->orWhereHasMorph('target', [Bundle::class], function ($q) {
                        $q->where('is_active', true)
                          ->where(fn($sub) => $sub->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                          ->where(fn($sub) => $sub->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
                    });
                })
                ->with(['target']) 
                ->orderBy('sort_order', 'asc')
                ->get();
        });
    }
}