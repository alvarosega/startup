<?php

namespace App\Actions\Admin\Brand;

use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DeleteBrandAction
{
    /**
     * Ejecuta el Soft Delete de la marca bajo protección Zero-Trust.
     */
    public function execute(Brand $brand): bool
    {
        return DB::transaction(function () use ($brand) {
            
            // REGLA 2.B: Verificación Zero-Trust
            if ($brand->products()->exists()) {
                throw new \Exception("DENEGADO: La marca tiene productos activos. Desvincúlelos primero.");
            }

            // CRÍTICO: Romper la caché al eliminar (Para métricas y listados)
            Cache::increment('admin_brands_version');
            
            // Purga de la imagen física si fuera necesario (Opcional en SoftDeletes)
            // if ($brand->image_path) Storage::disk('public')->delete($brand->image_path);

            return $brand->delete();
        });
    }
}