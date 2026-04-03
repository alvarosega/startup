<?php

namespace App\Actions\Customer\Featured;

use App\Models\{Product, Sku};
use App\Services\Finance\PriceResolverService;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class GetProductShowcaseAction
{
    public function __construct(
        private PriceResolverService $priceResolver
    ) {}

    public function execute(string $productSlug, string $branchId): array
    {
        $now = now();

        // 1. Obtener Producto Raíz
        $product = Product::where('slug', $productSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // 2. SKUs del Producto (Variantes principales)
        $mainSkus = Sku::with(['product.brand', 'prices' => fn($q) => $q->where('branch_id', $branchId)])
            ->leftJoin('inventory_balances as ib', fn($j) => $j->on('skus.id', '=', 'ib.sku_id')->where('ib.branch_id', $branchId))
            ->select(['skus.*', DB::raw('COALESCE(ib.total_physical - ib.total_reserved, 0) as available_stock')])
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->get()
            ->map(fn($sku) => $this->enrich($sku, $branchId, $now));

        // 3. Otros SKUs (Catálogo Global - Cursor Paginated)
        $others = Sku::with(['product.brand', 'prices' => fn($q) => $q->where('branch_id', $branchId)])
            ->leftJoin('inventory_balances as ib', fn($j) => $j->on('skus.id', '=', 'ib.sku_id')->where('ib.branch_id', $branchId))
            ->select(['skus.*', DB::raw('COALESCE(ib.total_physical - ib.total_reserved, 0) as available_stock')])
            ->where('product_id', '!=', $product->id)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->cursorPaginate(15)
            ->through(fn($sku) => $this->enrich($sku, $branchId, $now));

        return [
            'product' => $product,
            'skus' => $mainSkus,
            'others_paginated' => $others
        ];
    }

    private function enrich(Sku $sku, string $branchId, $now): Sku
    {
        $sku->resolved_price = $this->priceResolver->resolveWinningPrice($sku, 1, $now);
        return $sku;
    }
}