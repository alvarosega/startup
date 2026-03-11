<?php

namespace App\Actions\Admin\Price;

use App\Models\Price;
use Illuminate\Support\Facades\Cache; // <--- IMPORTAR CACHE

class DeletePriceAction
{
    public function execute(Price $price, string $adminId): void
    {
        // Auditoría antes del SoftDelete
        $price->update(['updated_by_id' => $adminId]);
        $price->delete();

        // LA LEY: Destruir la caché obsoleta
        Cache::forget('admin_products_list');
    }
}