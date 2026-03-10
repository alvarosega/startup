<?php
namespace App\Actions\Admin\Sku;

use App\Models\Sku;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class DeleteSkuAction
{
    public function execute(Sku $sku): void
    {
        // BLINDAJE ZERO-TRUST: Verificación de integridad física
        // Buscamos si la variante tiene lotes con stock positivo en cualquier sucursal.
        $hasStock = $sku->inventoryLots()->where('current_stock', '>', 0)->exists();

        if ($hasStock) {
            throw ValidationException::withMessages([
                'sku' => "BLOQUEO_SEGURIDAD: No se puede eliminar la variante '{$sku->code}' porque tiene existencias físicas activas."
            ]);
        }

        // El SoftDelete del modelo se encarga de deleted_at
        $sku->delete();
        
        // LA LEY: Invalida el catálogo general para que desaparezca del frontend
        Cache::forget('admin_products_list');
    }
}