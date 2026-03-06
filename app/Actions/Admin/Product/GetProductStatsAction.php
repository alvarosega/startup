<?php

namespace App\Actions\Admin\Product;

use App\Models\{Product, Sku};

class GetProductStatsAction
{
    /**
     * Ejecuta el conteo global del catálogo.
     * Al estar fuera del controlador, permite ser cacheado o movido a reportes fácilmente.
     */
    public function execute(): array
    {
        return [
            'total'      => Product::count(),
            'active'     => Product::where('is_active', true)->count(),
            'incomplete' => Product::has('skus', '=', 0)->count(),
            'total_skus' => Sku::count(),
        ];
    }
}