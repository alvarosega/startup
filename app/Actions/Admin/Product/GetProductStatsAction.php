<?php

declare(strict_types=1);

namespace App\Actions\Admin\Product;

use Illuminate\Support\Facades\DB;

class GetProductStatsAction
{
    public function execute(): array
    {
        $stats = DB::table('products')
            ->where('deleted_epoch', 0)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                (SELECT COUNT(*) FROM skus WHERE deleted_epoch = 0) as total_skus,
                COUNT(CASE WHEN NOT EXISTS (SELECT 1 FROM skus WHERE skus.product_id = products.id AND skus.deleted_epoch = 0) THEN 1 END) as incomplete
            ")
            ->first();

        return [
            'total'      => (int) ($stats->total ?? 0),
            'active'     => (int) ($stats->active ?? 0),
            'total_skus' => (int) ($stats->total_skus ?? 0),
            'incomplete' => (int) ($stats->incomplete ?? 0),
        ];
    }
}