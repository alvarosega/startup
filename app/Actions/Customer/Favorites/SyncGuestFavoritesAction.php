<?php

declare(strict_types=1);

namespace App\Actions\Customer\Favorites;

use App\Models\Customer\Favorite;
use Illuminate\Support\Facades\Auth;

final readonly class SyncGuestFavoritesAction
{
    public function execute(array $skuIds): void
    {
        $customerId = Auth::guard('customer')->id();

        foreach ($skuIds as $skuId) {
            // Usamos updateOrCreate para evitar errores de duplicidad si el SKU ya era favorito
            Favorite::updateOrCreate([
                'customer_id' => $customerId,
                'sku_id' => $skuId
            ]);
        }
    }
}