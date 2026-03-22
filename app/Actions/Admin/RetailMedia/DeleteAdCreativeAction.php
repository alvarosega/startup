<?php

namespace App\Actions\Admin\RetailMedia;

use App\Models\AdCreative;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class DeleteAdCreativeAction
{
    public function execute(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $creative = AdCreative::findOrFail($id);

            // 1. Limpieza de Disco (Evitar archivos huérfanos)
            if ($creative->image_mobile_path) {
                Storage::disk('public')->delete($creative->image_mobile_path);
            }
            if ($creative->image_desktop_path) {
                Storage::disk('public')->delete($creative->image_desktop_path);
            }

            // 2. Borrado físico (o Soft Delete según modelo)
            $creative->delete();

            // 3. Purga de Caché
            Cache::forget('ad_creatives_admin_index_*');

            return true;
        });
    }
}