<?php

declare(strict_types=1);

namespace App\Actions\Customer\Sku;

use App\Models\Sku;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class ListSkusAction
{
    public function execute(string $categoryId, string $branchId, ?string $customerId, array $filters): CursorPaginator
    {
        // 1. Árbol de categorías (Resuelve el agrupamiento huérfano extrayendo Padre + Hijas)
        $categoryIds = Category::where('id', $categoryId)
            ->orWhere('parent_id', $categoryId)
            ->pluck('id')
            ->toArray();

        // 2. Base de la consulta con protección de duplicidad de columnas
        $query = Sku::query()
            ->select('skus.*')
            ->where('skus.is_active', true)
            ->whereHas('product', fn($q) => $q->whereIn('category_id', $categoryIds));

        // 3. Filtro de Búsqueda
        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function (Builder $q) use ($term) {
                $q->where('skus.name', 'like', "%{$term}%")
                  ->orWhere('skus.code', 'like', "%{$term}%")
                  ->orWhereHas('product.brand', fn($bq) => $bq->where('name', 'like', "%{$term}%"));
            });
        }

        // 4. Resolución Logística en subconsulta (Stock)
        $query->addSelect([
            'available_stock' => DB::table('inventory_lots')
                ->selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                ->whereColumn('sku_id', 'skus.id')
                ->where('branch_id', $branchId)
                ->where('is_safety_stock', false)
        ])
        ->with([
            'product.brand', 
            'prices' => fn($q) => $q->where('branch_id', $branchId)
        ]);

        // 5. Motor de Ordenamiento (Sort)
        $sort = $filters['sort'] ?? 'relevance';
        
        if ($sort === 'price_asc' || $sort === 'price_desc') {
            $direction = $sort === 'price_asc' ? 'asc' : 'desc';
            
            // JOIN estricto para permitir que la base de datos ordene antes de paginar
            $query->join('prices', 'skus.id', '=', 'prices.sku_id')
                  ->where('prices.branch_id', $branchId)
                  ->orderBy('prices.final_price', $direction);
        } else {
            $query->orderBy('skus.sort_order', 'asc');
        }

        // Desempate obligatorio matemático para CursorPaginator
        $query->orderBy('skus.id', 'asc');

        // 6. Retorno de Paginador Cursor
        return $query->cursorPaginate(15)->withQueryString();
    }
}