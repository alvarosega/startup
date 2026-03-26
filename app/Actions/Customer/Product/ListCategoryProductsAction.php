<?php

declare(strict_types=1);

namespace App\Actions\Customer\Product;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\CursorPaginator;

class ListCategoryProductsAction
{
    public function execute(string $categoryId, string $branchId, array $filters): CursorPaginator
    {
        $now = now()->toDateTimeString();

        $query = DB::table('skus as s')
            ->select([
                's.id as sku_id',
                'p.id as product_id',
                's.name as sku_name',
                's.image_path as sku_image',
                's.sort_order as sku_sort',      // REQUERIDO PARA CURSOR
                'p.image_path as product_image',
                'p.is_alcoholic',
                'p.sort_order as product_sort',  // REQUERIDO PARA CURSOR
                'b.name as brand_name',
                'ib.total_physical',
                'ib.total_reserved',
                DB::raw("(
                    SELECT final_price FROM prices 
                    WHERE prices.sku_id = s.id 
                    AND prices.branch_id = ? 
                    AND prices.min_quantity = 1 
                    AND prices.valid_from <= ?
                    AND (prices.valid_to IS NULL OR prices.valid_to >= ?)
                    ORDER BY prices.priority DESC, prices.created_at DESC LIMIT 1
                ) as final_price"),
                DB::raw("(
                    SELECT list_price FROM prices 
                    WHERE prices.sku_id = s.id 
                    AND prices.branch_id = ? 
                    AND prices.min_quantity = 1 
                    AND prices.valid_from <= ?
                    AND (prices.valid_to IS NULL OR prices.valid_to >= ?)
                    ORDER BY prices.priority DESC, prices.created_at DESC LIMIT 1
                ) as list_price")
            ])
            ->setBindings([$branchId, $now, $now, $branchId, $now, $now], 'select')
            ->join('products as p', 's.product_id', '=', 'p.id')
            ->join('brands as b', 'p.brand_id', '=', 'b.id')
            ->leftJoin('inventory_balances as ib', function($join) use ($branchId) {
                $join->on('ib.sku_id', '=', 's.id')
                     ->whereRaw('ib.branch_id = ?', [$branchId]);
            })
            ->where(function($q) use ($categoryId) {
                $q->where('p.category_id', $categoryId)
                  ->orWhereIn('p.category_id', function($sub) use ($categoryId) {
                      $sub->select('id')->from('categories')->where('parent_id', $categoryId);
                  });
            })
            ->where('s.is_active', true)
            ->where('p.is_active', true);

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('s.name', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('p.name', 'LIKE', "%{$filters['search']}%");
            });
        }

        // Definición de ordenamiento
        $sort = $filters['sort'] ?? 'relevance';
        match ($sort) {
            'price_asc'  => $query->orderBy('final_price', 'asc'),
            'price_desc' => $query->orderBy('final_price', 'desc'),
            default      => $query->orderBy('sku_sort', 'asc')
                                  ->orderBy('product_sort', 'asc')
                                  ->orderBy('sku_name', 'asc')
        };

        return $query->cursorPaginate(20)->through(function ($row) {
            $finalPrice = (float) ($row->final_price ?? 0);
            $listPrice = (float) ($row->list_price ?? 0);
            $discount = ($listPrice > $finalPrice && $listPrice > 0) ? (($listPrice - $finalPrice) / $listPrice) * 100 : 0;

            return [
                'id'                  => (string) $row->sku_id,
                'product_id' => (string) $row->product_id,
                'name'                => (string) $row->sku_name,
                'brand_name'          => (string) $row->brand_name,
                'image'               => $row->sku_image ? asset('storage/' . $row->sku_image) : ($row->product_image ? asset('storage/' . $row->product_image) : null),
                'final_price'         => $finalPrice,
                'list_price'          => $listPrice,
                'discount_percentage' => (int) round($discount),
                'stock'               => (int) (($row->total_physical ?? 0) - ($row->total_reserved ?? 0)),
                'is_alcoholic'        => (bool) $row->is_alcoholic,
                // PERSISTENCIA PARA EL CURSOR (Laravel los requiere en el array resultante)
                'sku_sort'            => $row->sku_sort,
                'product_sort'        => $row->product_sort,
                'sku_name'            => $row->sku_name,
                'final_price_raw'     => $row->final_price // En caso de que se use para el cursor
            ];
        });
    }
}