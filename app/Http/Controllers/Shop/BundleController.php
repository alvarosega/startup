<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BundleController extends Controller
{
    /**
     * Galería de Packs para Clientes
     */
    public function index()
    {
        // Traemos solo los packs activos
        $bundles = Bundle::where('is_active', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy('fixed_price', 'asc') // O por popularidad si prefieres
            ->get();

        return Inertia::render('Shop/Bundles/Index', ['bundles' => $bundles]);
    }

    /**
     * Acción: Agregar Pack al Carrito ("Explosión de items")
     */
    public function addToCart(Request $request, Bundle $bundle)
    {
        $user = Auth::user();
        
        // 1. Obtener Carrito (Misma lógica que CartController)
        $cart = Cart::firstOrCreate(
            [
                'user_id' => $user ? $user->id : null, 
                'session_id' => $user ? null : $request->session()->getId()
            ],
            ['branch_id' => 1] // Aquí deberías usar la lógica de Geo-localización si la tienes
        );

        // 2. Iterar sobre el contenido del Pack e insertar items individuales
        foreach ($bundle->skus as $sku) {
            
            $quantityToAdd = $sku->pivot->quantity;

            // Buscar si ya existe ese producto en el carrito
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('sku_id', $sku->id)
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $quantityToAdd);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'sku_id' => $sku->id,
                    'quantity' => $quantityToAdd
                ]);
            }
        }

        return redirect()->route('cart.index')
            ->with('success', "¡Pack '{$bundle->name}' agregado! Puedes ajustar las cantidades aquí.");
    }
}