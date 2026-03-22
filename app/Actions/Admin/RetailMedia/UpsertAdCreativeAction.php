<?php

namespace App\Actions\Admin\RetailMedia;

use App\Models\AdCreative;
use App\DTOs\Admin\RetailMedia\UpsertAdCreativeDTO;
use Illuminate\Support\Facades\{DB, Storage, Cache};

class UpsertAdCreativeAction
{
    public function execute(UpsertAdCreativeDTO $dto): AdCreative
    {
        return DB::transaction(function () use ($dto) {
            $creative = $dto->id ? AdCreative::findOrFail($dto->id) : new AdCreative();

            $creative->fill([
                'campaign_id'  => $dto->campaign_id,
                'placement_id' => $dto->placement_id,
                'branch_id'    => $dto->branch_id,    // Guardado directo 1:N
                'category_id'  => $dto->category_id,  // Nuevo anclaje
                'target_type'  => $dto->target_type,
                'target_id'    => $dto->target_id,
                'name'         => $dto->name,
                'action_type'  => $dto->action_type,
                'sort_order'   => $dto->sort_order,
                'is_active'    => $dto->is_active,
            ]);

            if ($dto->image_mobile) {
                if ($creative->image_mobile_path) Storage::disk('public')->delete($creative->image_mobile_path);
                $creative->image_mobile_path = $dto->image_mobile->store('retail-media/mobile', 'public');
            }

            if ($dto->image_desktop) {
                if ($creative->image_desktop_path) Storage::disk('public')->delete($creative->image_desktop_path);
                $creative->image_desktop_path = $dto->image_desktop->store('retail-media/desktop', 'public');
            }

            $creative->save();
            
            Cache::flush(); // Limpieza agresiva para reflejar cambios en la App
            return $creative;
        });
    }
}