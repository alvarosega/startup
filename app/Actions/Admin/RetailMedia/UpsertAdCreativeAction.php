<?php

declare(strict_types=1);

namespace App\Actions\Admin\RetailMedia;

use App\Models\{AdCreative, AdPlacement};
use App\DTOs\Admin\RetailMedia\UpsertAdCreativeDTO;
use Illuminate\Support\Facades\{DB, Storage, Cache};

class UpsertAdCreativeAction
{
    public function execute(UpsertAdCreativeDTO $dto): AdCreative
    {
        return DB::transaction(function () use ($dto) {
            // BLOQUEO PESIMISTA: Validar capacidad del placement
            $placement = AdPlacement::where('id', $dto->placement_id)->lockForUpdate()->firstOrFail();
            
            $currentCount = AdCreative::where('placement_id', $placement->id)
                ->where('branch_id', $dto->branch_id)
                ->where('is_active', true)
                ->count();

            if (!$dto->id && $currentCount >= $placement->max_items) {
                throw new \Exception("Capacidad máxima agotada para el placement: {$placement->name}");
            }

            $creative = $dto->id ? AdCreative::findOrFail($dto->id) : new AdCreative();

            $creative->fill([
                'campaign_id'  => $dto->campaign_id,
                'placement_id' => $dto->placement_id,
                'branch_id'    => $dto->branch_id,
                'brand_id'     => $dto->brand_id,
                'category_id'  => $dto->category_id,
                'target_type'  => $dto->target_type,
                'target_id'    => $dto->target_id,
                'name'         => $dto->name,
                'action_type'  => $dto->action_type,
                'sort_order'   => $dto->sort_order,
                'is_active'    => $dto->is_active,
            ]);

            // Gestión de Silo de Archivos
            if ($dto->image_mobile) {
                if ($creative->image_mobile_path) Storage::disk('public')->delete($creative->image_mobile_path);
                $creative->image_mobile_path = $dto->image_mobile->store('retail-media/mobile', 'public');
            }

            if ($dto->image_desktop) {
                if ($creative->image_desktop_path) Storage::disk('public')->delete($creative->image_desktop_path);
                $creative->image_desktop_path = $dto->image_desktop->store('retail-media/desktop', 'public');
            }

            $creative->save();
            
            // Invalidación por Prefijos (Regla 4)
            Cache::forget("retail_media_branch_{$dto->branch_id}");
            
            return $creative;
        });
    }
}