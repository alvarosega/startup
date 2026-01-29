<?php

namespace App\Actions\Market;

use App\Models\MarketZone;
use App\Models\Category; // <--- Importante
use App\DTOs\MarketZoneDTO;
use Illuminate\Support\Facades\DB;

class UpsertMarketZoneAction
{
    public function execute(MarketZoneDTO $dto, ?MarketZone $zone = null): MarketZone
    {
        return DB::transaction(function () use ($dto, $zone) {
            
            $data = [
                'name' => $dto->name,
                'slug' => $dto->slug,
                'hex_color' => $dto->hexColor,
                'svg_id' => $dto->svgId,
                'description' => $dto->description,
            ];

            if ($zone) {
                $zone->update($data);
            } else {
                $zone = MarketZone::create($data);
            }

            // GESTIÓN DE CATEGORÍAS (Solo si se envían datos)
            // 1. Limpiamos las categorías que antes eran de esta zona pero ya no están en la lista
            Category::where('market_zone_id', $zone->id)
                ->whereNotIn('id', $dto->categories)
                ->update(['market_zone_id' => null]);

            // 2. Asignamos las nuevas categorías seleccionadas a esta zona
            if (!empty($dto->categories)) {
                Category::whereIn('id', $dto->categories)
                    ->update(['market_zone_id' => $zone->id]);
            }

            return $zone;
        });
    }
}