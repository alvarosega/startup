<?php

namespace App\Actions\Admin\MarketZone;

use App\DTOs\Admin\MarketZone\MarketZoneDTO;
use App\Models\MarketZone;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UpsertMarketZoneAction
{
    

    public function execute(MarketZoneDTO $dto, ?MarketZone $zone = null): MarketZone
    {
        return DB::transaction(function () use ($dto, $zone) {
            $zone = MarketZone::updateOrCreate(
                ['id' => $zone?->id],
                [
                    'name' => $dto->name,
                    'slug' => $dto->slug,
                    'hex_color' => $dto->hexColor,
                    'svg_id' => $dto->svgId,
                    'description' => $dto->description,
                ]
            );

            // 1. Desvincular TODAS las que pertenecen a esta zona actualmente
            Category::where('market_zone_id', $zone->id)->update(['market_zone_id' => null]);

            // 2. Vincular las nuevas (esto maneja el movimiento desde otras zonas automáticamente)
            if (!empty($dto->categories)) {
                Category::whereIn('id', $dto->categories)->update(['market_zone_id' => $zone->id]);
            }

            // 3. LIMPIEZA DE CACHÉ (Regla de Rendimiento Extremo)
            try {
                if (method_exists(Cache::getStore(), 'tags')) {
                    Cache::tags(['catalog', 'market_zones'])->flush();
                } else {
                    // Fallback para entornos sin Redis (Desarrollo/Local)
                    Cache::forget('catalog_home_data'); 
                }
            } catch (\Exception $e) {
                // Silenciamos fallos de caché para no romper la atomicidad de la DB
                report($e);
            }

            return $zone;
        });
    }
}