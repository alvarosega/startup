<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        DB::transaction(function () use ($product) {
            Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            $hasPhysicalStock = DB::table('inventory_lots')
                ->whereIn('sku_id', function ($query) use ($product) {
                    $query->select('id')->from('skus')->where('product_id', $product->id);
                })
                ->where('current_stock', '>', 0)
                ->exists();

            if ($hasPhysicalStock) {
                throw ValidationException::withMessages([
                    'product' => 'BLOQUEO_SEGURIDAD: Operación cancelada. El producto maestro posee variantes con existencias físicas en almacén.'
                ]);
            }

            $product->skus()->update(['is_active' => false]);
            
            foreach ($product->skus as $sku) {
                $sku->delete();
            }

            $product->update(['is_active' => false]);
            $product->delete();
        });
    }
}