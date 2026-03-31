<?php

declare(strict_types=1);

namespace App\Actions\Customer\Featured;

use App\Models\{Product, Sku};
use Illuminate\Pagination\CursorPaginator;

final readonly class GetProductShowcaseAction
{
    public function execute(string $slug): array
    {
        // 1. Identidad Maestra
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // 2. Stack Vertical de Variantes (Propio)
        $currentSkus = Sku::where('product_id', $product->id)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        // 3. Feed Global (Otros) - CursorPaginate para Infinite Scroll
        $others = Sku::where('product_id', '!=', $product->id)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->cursorPaginate(15);

        return [
            'product' => $product,
            'skus' => $currentSkus,
            'others' => $others
        ];
    }
}