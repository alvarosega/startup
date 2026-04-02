<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Favorites;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sku;
use App\Http\Resources\Customer\Favorite\FavoriteProductResource;
use App\Http\Resources\Customer\Favorite\FavoriteSkuResource;
use App\Actions\Customer\Favorites\ToggleFavoriteAction;
use App\Actions\Customer\Favorites\SyncGuestFavoritesAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\{Inertia, Response};

final class FavoriteController extends Controller
{
    public function index(Request $request): Response
    {
        $customerId = Auth::guard('customer')->id();
        $activeProductId = $request->query('active_id');
        $guestIds = $request->query('ids', []);

        // 1. CARGA DE PRODUCTOS (Para el carrusel superior)
        $query = Product::query()->with(['brand'])->withCount(['skus' => fn($q) => $q->active()]);

        if ($customerId) {
            $query->whereHas('favoritedBy', fn($q) => $q->where('customer_id', $customerId));
        } else {
            $query->whereIn('id', $guestIds);
        }

        $favoriteProducts = $query->get();
        
        // Determinar cuál es el producto activo
        $selectedId = $activeProductId ?? $favoriteProducts->first()?->id;
        
        // 2. CARGA DE SKUS (Para la grilla inferior)
        $activeSkus = collect();
        if ($selectedId) {
            $activeSkus = Sku::where('product_id', $selectedId)
                ->active()
                ->orderBy('sort_order', 'asc')
                ->with(['product.brand', 'prices']) // 'prices' es vital para el PriceResolverService
                ->get();
        }

        return Inertia::render('Customer/Favorites/Index', [
            'favoriteProducts' => FavoriteProductResource::collection($favoriteProducts)->resolve(),
            'activeSkus'       => FavoriteSkuResource::collection($activeSkus)->resolve(),
            'selectedId'       => $selectedId
        ]);
    }

    public function toggle(Request $request, ToggleFavoriteAction $action)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $action->execute($request->product_id);
        return back();
    }

    public function sync(Request $request, SyncGuestFavoritesAction $action)
    {
        $request->validate(['product_ids' => 'required|array']);
        $action->execute($request->product_ids);
        return back();
    }
}