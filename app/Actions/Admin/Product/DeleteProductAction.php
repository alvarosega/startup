<?php
namespace App\Actions\Admin\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->skus()->delete(); // Soft delete SKUs
            $product->delete(); // Soft delete Maestro
        });
    }
}