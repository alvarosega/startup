<?php

namespace App\Actions\Admin\RetailMedia;

use App\Models\AdCreative;
use App\DTOs\Admin\RetailMedia\AdCreativeFilterDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdCreativesAction
{
    public function execute(AdCreativeFilterDTO $dto): LengthAwarePaginator
    {
        // Generar llave de caché única basada en el DTO para evitar colisiones
        $cacheKey = 'ad_creatives_admin_index_' . md5(json_encode($dto));

        return Cache::remember($cacheKey, 3600, function () use ($dto) {
            return AdCreative::query()
                ->with([
                    'campaign.provider:id,commercial_name', 
                    'campaign.marketZone:id,name,hex_color',
                    'placement:id,name,code',
                    'sku:id,name',
                    'branches:id,name'
                ])
                ->when($dto->placement_code, fn($q) => 
                    $q->whereHas('placement', fn($p) => $p->where('code', $dto->placement_code))
                )
                ->when($dto->market_zone_id, fn($q) => 
                    $q->whereHas('campaign', fn($c) => $c->where('market_zone_id', $dto->market_zone_id))
                )
                ->when($dto->branch_id, fn($q) => 
                    $q->whereHas('branches', fn($b) => $b->where('branches.id', $dto->branch_id))
                )
                ->when(!is_null($dto->is_active), fn($q) => $q->where('is_active', $dto->is_active))
                ->orderBy('sort_order', 'asc')
                ->paginate($dto->per_page);
        });
    }
}