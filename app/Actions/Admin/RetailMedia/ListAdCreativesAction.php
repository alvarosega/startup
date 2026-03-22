<?php

namespace App\Actions\Admin\RetailMedia;

use App\Models\AdCreative;
use App\DTOs\Admin\RetailMedia\AdCreativeFilterDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ListAdCreativesAction
{
    public function execute(AdCreativeFilterDTO $dto): LengthAwarePaginator
    {
        // LA LEY: Cache dinámico basado en el estado del DTO
        $cacheKey = 'ad_creatives_admin_index_' . md5(serialize($dto));

        return Cache::remember($cacheKey, 3600, function () use ($dto) {
            return AdCreative::query()
                ->with([
                    'campaign.provider', 
                    'placement', 
                    'category:id,name', 
                    'branch:id,name',   
                    'target' // Carga polimórfica (SKU o Bundle)
                ])
                ->when($dto->placement_code, function ($q, $code) {
                    $q->whereHas('placement', fn($sq) => $sq->where('code', $code));
                })
                ->when($dto->branch_id, fn($q, $id) => $q->where('branch_id', $id))
                ->when($dto->category_id, fn($q, $id) => $q->where('category_id', $id))
                ->when($dto->is_active !== null, fn($q) => $q->where('is_active', $dto->is_active))
                ->orderBy('sort_order')
                // Usamos el parámetro de paginación del DTO
                ->paginate($dto->per_page);
        });
    }
}