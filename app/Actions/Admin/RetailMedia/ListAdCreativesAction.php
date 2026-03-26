<?php

declare(strict_types=1);

namespace App\Actions\Admin\RetailMedia;

use App\Models\AdCreative;
use App\DTOs\Admin\RetailMedia\AdCreativeFilterDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ListAdCreativesAction
{
    public function execute(AdCreativeFilterDTO $dto): LengthAwarePaginator
    {
        $cacheKey = 'ad_creatives_admin_list_' . md5(json_encode([
            $dto->placement_code, $dto->branch_id, $dto->category_id, $dto->is_active, $dto->per_page
        ]));

        return Cache::remember($cacheKey, 3600, function () use ($dto) {
            return AdCreative::query()
                ->with([
                    'campaign.provider', 
                    'placement', 
                    'category', 
                    'branch:id,name',
                    // Cargamos las relaciones explícitas sin el filtro roto
                    'sku.product:id,name', 
                    'bundle:id,name,type'
                ])
                ->when($dto->placement_code, function ($q, $code) {
                    $q->whereHas('placement', fn($sq) => $sq->where('code', $code));
                })
                ->when($dto->branch_id, fn($q, $id) => $q->where('branch_id', $id))
                ->when($dto->category_id, fn($q, $id) => $q->where('category_id', $id))
                ->when($dto->is_active !== null, fn($q) => $q->where('is_active', $dto->is_active))
                ->orderBy('placement_id') // Agrupa por ubicación primero
                ->orderBy('sort_order', 'asc') // Luego ordena internamente
                ->orderBy('created_at', 'desc')
                ->paginate($dto->per_page ?? 15);
        });
    }
}