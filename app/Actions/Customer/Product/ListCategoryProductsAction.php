<?php

declare(strict_types=1);

namespace App\Actions\Customer\Product;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\CursorPaginator;
use App\Http\Resources\Customer\Product\SkuResource;

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
                's.sort_order as sku_sort',
                'p.image_path as product_image',
                'p.is_alcoholic',
                'p.sort_order as product_sort',
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

        $sort = $filters['sort'] ?? 'relevance';
        match ($sort) {
            'price_asc'  => $query->orderBy('final_price', 'asc'),
            'price_desc' => $query->orderBy('final_price', 'desc'),
            default => $query->orderBy('sku_sort', 'asc')
                            ->orderBy('product_sort', 'asc')
                            ->orderBy('sku_name', 'asc')
        };

        return $query->cursorPaginate(20)->through(function ($row) {
            // LEY DE CENTRALIZACIÓN: Delegamos la transformación (y el placeholder) al Resource
            return (new SkuResource($row))->resolve();
        });
    }
}