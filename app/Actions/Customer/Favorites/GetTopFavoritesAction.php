<?php

declare(strict_types=1);

namespace App\Actions\Customer\Favorites;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

final readonly class GetTopFavoritesAction
{
    /**
     * Recupera los productos favoritos del cliente logueado.
     * La relación es a nivel Producto (favorites -> products).
     */
    public function execute(): Collection
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return collect();
        }

        return $customer->favorites()
            ->with(['brand'])
            ->withCount(['skus' => fn($q) => $q->where('is_active', true)])
            ->latest('favorites.created_at')
            ->limit(15) // Límite para el carrusel de la Home
            ->get();
    }
}