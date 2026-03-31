<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Favorites;

use App\Http\Controllers\Controller;
use App\Models\Customer\Favorite;
use App\Http\Resources\Customer\Featured\ProductShowcaseResource; // Reutilizando formateo de SkuCard
use Illuminate\Support\Facades\Auth;
use Inertia\{Inertia, Response};

final class FavoriteController extends Controller
{
    public function index(): \Inertia\Response
    {
        $favorites = [];
    
        // Solo consultamos la base de datos si hay una identidad confirmada
        if (\Illuminate\Support\Facades\Auth::guard('customer')->check()) {
            $rawFavorites = \App\Models\Customer\Favorite::with(['sku.product.brand'])
                ->where('customer_id', \Illuminate\Support\Facades\Auth::guard('customer')->id())
                ->orderBy('created_at', 'desc')
                ->get()
                ->pluck('sku');
    
            $favorites = \App\Http\Resources\Customer\Featured\ProductShowcaseResource::collection($rawFavorites);
        }
    
        return \Inertia\Inertia::render('Customer/Favorites/Index', [
            'favorites' => $favorites
        ]);
    }
    public function toggle(Request $request, App\Actions\Customer\Favorites\ToggleFavoriteAction $action)
    {
        $request->validate(['sku_id' => 'required|exists:skus,id']);
        $action->execute($request->sku_id);
        
        return back()->with('success', 'Radar de favoritos actualizado.');
    }
    public function sync(Request $request, SyncGuestFavoritesAction $action)
    {
        $request->validate(['sku_ids' => 'required|array']);
        $action->execute($request->sku_ids);
        
        // Retornamos una respuesta silenciosa para no interrumpir el flujo de redirección del login
        return back(); 
    }
}