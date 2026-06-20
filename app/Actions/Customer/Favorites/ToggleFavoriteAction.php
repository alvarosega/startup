<?php

declare(strict_types=1);

namespace App\Actions\Customer\Favorites;

use App\Models\Customer\Favorite;
use Illuminate\Support\Facades\Auth;

final readonly class ToggleFavoriteAction
{
    public function execute(string $productId): array
    {
        $customer = \Illuminate\Support\Facades\Auth::guard('customer')->user();
        
        // El método toggle añade si no existe, o quita si existe.
        $result = $customer->favorites()->toggle($productId);

        return [
            'attached' => count($result['attached']) > 0,
            'status'   => count($result['attached']) > 0 ? 'added' : 'removed'
        ];
    }
}