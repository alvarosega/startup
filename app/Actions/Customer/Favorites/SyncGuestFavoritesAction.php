<?php

declare(strict_types=1);

namespace App\Actions\Customer\Favorites;

use App\Models\Customer\Favorite;
use Illuminate\Support\Facades\Auth;

class SyncGuestFavoritesAction
{
    public function execute(array $skuIds): void
    {
        $customerId = Auth::guard('customer')->id();
        if (!$customerId || empty($skuIds)) return;

        $now = now();
        $insertData = [];
        
        $existingIds = Favorite::where('customer_id', $customerId)
            ->whereIn('sku_id', $skuIds)
            ->pluck('sku_id')
            ->toArray();

        foreach ($skuIds as $id) {
            if (!in_array($id, $existingIds)) {
                $insertData[] = [
                    'customer_id' => $customerId,
                    'sku_id'      => $id,
                    'created_at'  => $now,
                    'updated_at'  => $now
                ];
            }
        }

        if (!empty($insertData)) {
            Favorite::insert($insertData);
        }
    }
}