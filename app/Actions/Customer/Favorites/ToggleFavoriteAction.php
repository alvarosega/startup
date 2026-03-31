<?php

declare(strict_types=1);

namespace App\Actions\Customer\Favorites;

use App\Models\Customer\Favorite;
use Illuminate\Support\Facades\Auth;

final readonly class ToggleFavoriteAction
{
    public function execute(string $skuId): bool
    {
        $customerId = Auth::guard('customer')->id();
        
        $favorite = Favorite::where('customer_id', $customerId)
            ->where('sku_id', $skuId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return false; // Quitado
        }

        Favorite::create([
            'customer_id' => $customerId,
            'sku_id' => $skuId
        ]);

        return true; // Añadido
    }
}