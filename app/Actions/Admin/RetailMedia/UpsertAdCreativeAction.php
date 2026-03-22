<?php

namespace App\Actions\Admin\RetailMedia;

use App\Models\AdCreative;
use App\DTOs\Admin\RetailMedia\UpsertAdCreativeDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class UpsertAdCreativeAction
{
    public function execute(UpsertAdCreativeDTO $dto): AdCreative
    {
        return DB::transaction(function () use ($dto) {
            // 1. Buscamos o creamos la instancia sin guardar aún
            $creative = $dto->id ? AdCreative::findOrFail($dto->id) : new AdCreative();
    
            // 2. Mapeamos datos básicos
            $creative->fill([
                'campaign_id'  => $dto->campaign_id,
                'placement_id' => $dto->placement_id,
                'sku_id'       => $dto->sku_id,
                'name'         => $dto->name,
                'sort_order'   => $dto->sort_order,
                'is_active'    => $dto->is_active,
            ]);
    
            // 3. Procesamos imágenes ANTES del primer save en creación
            if ($dto->image_mobile) {
                if ($creative->image_mobile_path) Storage::disk('public')->delete($creative->image_mobile_path);
                $creative->image_mobile_path = $dto->image_mobile->store('retail-media/mobile', 'public');
            }
    
            if ($dto->image_desktop) {
                if ($creative->image_desktop_path) Storage::disk('public')->delete($creative->image_desktop_path);
                $creative->image_desktop_path = $dto->image_desktop->store('retail-media/desktop', 'public');
            }
    
            // 4. Ahora sí, guardamos (Insert o Update)
            $creative->save();
    
            // 5. Relaciones y Caché
            $creative->branches()->sync($dto->branch_ids);
            Cache::forget('ad_creatives_admin_index_*');
    
            return $creative;
        });
    }
}