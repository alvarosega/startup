<?php

declare(strict_types=1);

namespace App\Actions\Admin\RetailMedia;

use App\Models\AdCreative;
use App\DTOs\Admin\RetailMedia\AdCreativeFilterDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ListAdCreativesAction
{
    /**
     * Ejecuta el listado bajo el Protocolo de Capas.
     * Implementa Eager Loading masivo para evitar Round-trips a la DB.
     */
    public function execute(AdCreativeFilterDTO $dto): LengthAwarePaginator
    {
        // Regla 4: Sistema de Identificación de Caché por Silo
        $cacheKey = "retail_media_admin_list_branch_{$dto->branch_id}_p_{$dto->placement_code}_" . 
                    md5(serialize($dto));

        // Bajamos el TTL a 600s (10 min) para Administración o usamos invalidación activa
        return Cache::remember($cacheKey, 600, function () use ($dto) {
            return AdCreative::query()
                ->with([
                    'campaign.provider', 
                    'placement', 
                    'category', 
                    'brand:id,name,slug', // RELACIÓN OBLIGATORIA
                    'branch:id,name',
                    'sku.product:id,name', 
                    'bundle:id,name,type'
                ])
                // Optimizamos: Si ya tenemos el ID de placement en el DTO, evitamos el whereHas
                ->when($dto->placement_code, function ($q, $code) {
                    $q->whereHas('placement', fn($sq) => $sq->where('code', $code));
                })
                ->when($dto->branch_id, fn($q, $id) => $q->where('branch_id', $id))
                ->when($dto->category_id, fn($q, $id) => $q->where('category_id', $id))
                ->when($dto->is_active !== null, fn($q) => $q->where('is_active', $dto->is_active))
                
                // LEY DE CONSULTAS: El orden debe coincidir con los índices compuestos
                ->orderBy('placement_id') 
                ->orderBy('sort_order', 'asc') 
                ->orderBy('created_at', 'desc')
                ->paginate($dto->per_page ?? 15);
        });
    }
}