<?php

namespace App\Actions\Admin\Product;

use Illuminate\Support\Facades\DB;

class GetProductStatsAction
{
    public function execute(): array
    {
        $stats = DB::table('products')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                (SELECT COUNT(*) FROM skus) as total_skus,
                COUNT(CASE WHEN NOT EXISTS (SELECT 1 FROM skus WHERE skus.product_id = products.id) THEN 1 END) as incomplete
            ")
            ->first();

        return [
            'total'      => (int) $stats->total,
            'active'     => (int) $stats->active,
            'total_skus' => (int) $stats->total_skus,
            'incomplete' => (int) $stats->incomplete,
        ];
    }
}