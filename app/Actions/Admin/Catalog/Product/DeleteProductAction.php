<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteProductAction
{
    /**
     * Procesa de forma transaccional el borrado lógico del producto maestro evaluando dependencias jerárquicas directas.
     */
    public function execute(Product $product): void
    {
        DB::transaction(function () use ($product) {
            Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            // RECTIFICACIÓN: El test exige bloquear la remoción ante la simple presencia de variantes comerciales (SKUs) activas en el Workspace
            $hasActiveSkus = $product->skus()->where('is_active', true)->exists();

            if ($hasActiveSkus) {
                throw ValidationException::withMessages([
                    'product' => 'BLOQUEO_SEGURIDAD: Operación cancelada. El producto maestro posee variantes comerciales activas vinculadas.'
                ]);
            }

            // Fallback relacional de desactivación y purga para variantes huérfanas inactivas
            $product->skus()->update(['is_active' => false]);
            foreach ($product->skus as $sku) {
                $sku->delete();
            }

            $product->update(['is_active' => false]);
            $product->delete();
        });
    }
}