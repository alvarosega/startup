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

        // 1. Obtención Atómica del Producto
        $product = Product::where('slug', $productSlug)->where('is_active', true)->firstOrFail();

        // 2. Query Base con Join a Balances (Hostinger Optimal)
        $queryBase = Sku::with(['product.brand', 'prices' => fn($q) => $q->where('branch_id', $branchId)])
            ->leftJoin('inventory_balances as ib', fn($j) => 
                $j->on('skus.id', '=', 'ib.sku_id')->where('ib.branch_id', $branchId)
            )
            ->select([
                'skus.*',
                'ib.total_physical', // REQUISITO PARA EL ACCESSOR
                'ib.total_reserved'  // REQUISITO PARA EL ACCESSOR
            ])
            ->where('is_active', true);

        // 3. Segmentación de Resultados
        $mainSkus = (clone $queryBase)->where('product_id', $product->id)
            ->get()
            ->map(fn($sku) => $this->enrich($sku, $branchId, $now));

        $others = (clone $queryBase)->where('product_id', '!=', $product->id)
            ->orderBy('sort_order', 'asc')
            ->cursorPaginate(15) // PROTOCOLO ALTA DENSIDAD
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