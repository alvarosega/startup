<?php

declare(strict_types=1);

namespace App\Actions\Customer\Product;

use Illuminate\Support\Facades\DB;

class GetProductVariantsAction
{
    public function execute(string $productId, string $branchId): array
    {
        $now = now()->toDateTimeString();

        // 1. Datos del Padre (Enriquecido)
        $product = DB::table('products as p')
            ->select(['p.id', 'p.name', 'p.description', 'p.image_path', 'b.name as brand_name'])
            ->join('brands as b', 'p.brand_id', '=', 'b.id')
            ->where('p.id', $productId)
            ->first();

        if (!$product) abort(404);

        // 2. Todos los SKUs Hermanos
        $variants = DB::table('skus as s')
            ->select([
                's.id', 's.name', 's.image_path', 's.sort_order',
                'ib.total_physical', 'ib.total_reserved',
                DB::raw("(SELECT final_price FROM prices WHERE prices.sku_id = s.id AND prices.branch_id = ? AND prices.min_quantity = 1 AND prices.valid_from <= ? AND (prices.valid_to IS NULL OR prices.valid_to >= ?) ORDER BY prices.priority DESC LIMIT 1) as final_price"),
                DB::raw("(SELECT list_price FROM prices WHERE prices.sku_id = s.id AND prices.branch_id = ? AND prices.min_quantity = 1 AND prices.valid_from <= ? AND (prices.valid_to IS NULL OR prices.valid_to >= ?) ORDER BY prices.priority DESC LIMIT 1) as list_price")
            ])
            ->leftJoin('inventory_balances as ib', function($join) use ($branchId) {
                $join->on('ib.sku_id', '=', 's.id')->whereRaw('ib.branch_id = ?', [$branchId]);
            })
            ->where('s.product_id', $productId)
            ->where('s.is_active', true)
            ->orderBy('s.sort_order', 'asc')
            ->setBindings([$branchId, $now, $now, $branchId, $now, $now], 'select')
            ->get();

        return [
            'product' => [
                'id' => (string) $product->id,
                'name' => $product->name,
                'brand' => $product->brand_name,
                'description' => $product->description,
                'main_image' => $product->image_path ? asset('storage/' . $product->image_path) : null,
            ],
            'variants' => $variants->map(fn($v) => [
                'id' => (string) $v->id,
                'name' => $v->name,
                'image' => $v->image_path ? asset('storage/' . $v->image_path) : asset('storage/' . $product->image_path),
                'final_price' => (float) ($v->final_price ?? 0),
                'list_price' => (float) ($v->list_price ?? 0),
                'stock' => (int) (($v->total_physical ?? 0) - ($v->total_reserved ?? 0)),
            ])
        ];
    }
}