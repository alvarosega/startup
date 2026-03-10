<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;
use Illuminate\Support\Facades\{DB, Cache};
use Illuminate\Validation\ValidationException;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        // LA LEY: Verificación de Integridad Física antes del borrado
        // Si el producto tiene SKUs con lotes de inventario, bloqueamos.
        $hasStock = $product->skus()->whereHas('inventoryLots', function($q) {
            $q->where('current_stock', '>', 0);
        })->exists();

        if ($hasStock) {
            throw ValidationException::withMessages([
                'product' => "BLOQUEO_SEGURIDAD: No es posible eliminar el maestro '{$product->name}' porque existen existencias físicas activas en el inventario."
            ]);
        }

        DB::transaction(function () use ($product) {
            // Borrado en cascada suave (SoftDelete)
            $product->skus()->delete(); 
            $product->delete();
            
            // DoD v2.0: Invalida la caché del catálogo
            Cache::forget('admin_products_list');
        });
    }
}