<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteProductAction
{
    public function execute(string $productId): void
    {
        DB::transaction(function () use ($productId) {
            // BLOQUEO PESIMISTA: Nadie toca este producto mientras decidimos su baja
            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            // LA LEY: Bloqueo de seguridad por existencias
            $hasPhysicalStock = DB::table('inventory_lots')
                ->whereIn('sku_id', function($query) use ($productId) {
                    $query->select('id')->from('skus')->where('product_id', $productId);
                })
                ->where('current_stock', '>', 0)
                ->exists();

            if ($hasPhysicalStock) {
                throw ValidationException::withMessages([
                    'product' => 'BLOQUEO_SEGURIDAD: No es posible dar de baja un producto con stock activo en almacén.'
                ]);
            }

            // ACCIÓN: Hibernación Atómica (Desactivar + SoftDelete)
            // Desactivamos SKUs para que desaparezcan de preventas/compras inmediatamente
            $product->skus()->update(['is_active' => false]);
            $product->skus()->delete(); // SoftDelete (Lógica de recuperación)

            $product->update(['is_active' => false]);
            $product->delete(); // SoftDelete del Maestro
        });
    }
}