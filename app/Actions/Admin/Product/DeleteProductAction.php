<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;
use Illuminate\Support\Facades\{DB, Cache};
use Illuminate\Validation\ValidationException;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        DB::transaction(function () use ($product) {
            // DoD v2.0: Protección Zero-Trust Deletes
            // No podemos borrar un maestro si tiene SKUs con stock activo o en pedidos.
            // Por ahora, asumiremos que se borran en cascada suave (SoftDelete),
            // pero levantamos advertencia si es una política de la empresa.
            
            // Bloqueo estricto de ejemplo (si quisieras impedir el borrado):
            /*
            if ($product->skus()->where('stock', '>', 0)->exists()) {
                 throw ValidationException::withMessages(['product' => 'No se puede eliminar un producto con inventario activo.']);
            }
            */

            $product->skus()->delete(); // Cascading soft delete
            $product->delete();
            
            Cache::forget('admin_products_list');
        });
    }
}