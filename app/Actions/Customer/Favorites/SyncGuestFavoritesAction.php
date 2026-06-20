<?php

declare(strict_types=1);

namespace App\Actions\Customer\Favorites;

use App\Models\Customer\Favorite;
use Illuminate\Support\Facades\Auth;

class SyncGuestFavoritesAction
{
    public function execute(array $productIds): void // <--- Cambiado de skuIds
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer || empty($productIds)) return;

        // Usamos el método syncWithoutDetaching para ser calculadores y eficientes
        // Esto evita duplicados y respeta los registros existentes en una sola query.
        $customer->favorites()->syncWithoutDetaching($productIds);
    }
}