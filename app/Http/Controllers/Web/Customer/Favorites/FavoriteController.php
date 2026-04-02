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
use Illuminate\Http\Request; // <--- SOLUCIÓN AL ERROR CRÍTICO 500
use Illuminate\Support\Facades\Auth;
use Inertia\{Inertia, Response};

final class FavoriteController extends Controller
{
    public function index(Request $request): Response
    {
        $customerId = Auth::guard('customer')->id();
        $activeProductId = $request->query('active_id');
        
        // CAPTURA HÍBRIDA: Si es invitado, buscamos los IDs que vienen del LocalStorage
        $guestIds = $request->query('ids', []);

        $query = Product::query()->with(['brand'])->withCount(['skus' => fn($q) => $q->active()]);

        if ($customerId) {
            $query->whereHas('favoritedBy', fn($q) => $q->where('customer_id', $customerId));
        } else {
            $query->whereIn('id', $guestIds);
        }

        $favoriteProducts = $query->get();
        $selectedId = $activeProductId ?? $favoriteProducts->first()?->id;
        
        $activeSkus = $selectedId 
            ? Sku::where('product_id', $selectedId)->active()->orderBy('sort_order')->with(['product.brand', 'prices'])->get()
            : collect();

        return Inertia::render('Customer/Favorites/Index', [
            'favoriteProducts' => FavoriteProductResource::collection($favoriteProducts)->resolve(),
            'activeSkus'       => FavoriteSkuResource::collection($activeSkus)->resolve(), // <--- CAMBIO CLAVE
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