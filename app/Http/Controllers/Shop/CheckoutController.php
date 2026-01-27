<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Cart;
use App\Models\InventoryLot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Arquitectura
use App\Http\Requests\Shop\StoreOrderRequest;
use App\DTOs\Shop\CreateOrderDTO;
use App\Actions\Shop\CreateOrderAction;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // 1. Buscar el carrito del usuario (Sin filtrar por sucursal aún)
        $cart = Cart::where('user_id', $user->id)
            ->with(['items.sku.product', 'items.sku.prices', 'branch'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('shop.index');
        }

        // --- LÓGICA DE MIGRACIÓN DE SUCURSAL ---
        // Si el usuario está en una sucursal distinta a la del carrito, 
        // movemos el carrito a la nueva sucursal para validar stock/precios allí.
        if ($cart->branch_id !== $user->branch_id) {
            $cart->update(['branch_id' => $user->branch_id]);
            // Recargamos la relación branch para la vista
            $cart->load('branch'); 
        }
        // ---------------------------------------

        // --- LÓGICA DE AUTO-SANACIÓN DE STOCK ---
        $itemsRemoved = [];
        $hasAdjustments = false;

        foreach ($cart->items as $item) {
            
            // Calculamos stock disponible real EN LA SUCURSAL ACTUAL DEL USUARIO
            $stockAvailable = InventoryLot::where('branch_id', $cart->branch_id)
                ->where('sku_id', $item->sku_id)
                ->sum(DB::raw('quantity - reserved_quantity'));

            // CASO A: Stock 0 -> Eliminar
            if ($stockAvailable <= 0) {
                $itemsRemoved[] = $item->sku->name;
                $item->delete();
                $hasAdjustments = true;
                continue;
            }

            // CASO B: Stock menor al pedido -> Ajustar
            if ($item->quantity > $stockAvailable) {
                $item->update(['quantity' => $stockAvailable]);
                $hasAdjustments = true;
            }
        }

        // Si hubo cambios drásticos (se borraron cosas), avisamos y redirigimos
        if ($hasAdjustments) {
            // Refrescamos items restantes
            $cart->refresh(); 

            if ($cart->items->count() === 0) {
                return redirect()->route('shop.index')
                    ->with('error', 'Al cambiar de sucursal, los productos de tu carrito ya no están disponibles.');
            }

            return redirect()->route('cart.index')
                ->with('error', 'El inventario de esta sucursal es diferente. Hemos ajustado tu carrito a las existencias locales.');
        }
        // ---------------------------------------

        // Calcular totales con los precios de la SUCURSAL ACTUAL
        $items = $cart->items->map(function ($item) use ($cart) {
            // Obtenemos el precio correspondiente a la sucursal donde está el carrito ahora
            $price = $item->sku->getCurrentPrice($cart->branch_id);
            
            return [
                'name' => $item->sku->product->name . ' ' . $item->sku->name,
                'quantity' => $item->quantity,
                'unit_price' => $price,
                'subtotal' => $price * $item->quantity
            ];
        });

        return Inertia::render('Shop/Checkout/Index', [
            'cart' => [
                'items' => $items,
                'total' => $items->sum('subtotal'),
                'branch_id' => $cart->branch_id, // Para validación en frontend
                'branch_name' => $cart->branch->name ?? 'Sucursal Actual'
            ],
            'addresses' => $user->addresses,
            // Enviamos el ID como string (UUID) o null si no hay default
            'default_address_id' => $user->addresses()->where('is_default', true)->value('id')
        ]);
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $action)
    {
        try {
            // El DTO tomará el branch_id del usuario.
            // Gracias al index(), el carrito ya debería tener el mismo branch_id en la BD.
            $dto = CreateOrderDTO::fromRequest($request);
            $order = $action->execute($dto);

            return redirect()->route('orders.show', $order->code)
                ->with('success', 'Stock reservado por 5 minutos. ¡Sube tu comprobante!');

        } catch (\Exception $e) {
            // Si falla (ej: cambió de sucursal en otra pestaña justo antes de dar click), 
            // lo mandamos al index del checkout para que la lógica de migración se ejecute de nuevo.
            return redirect()->route('checkout.index')
                ->withErrors(['error' => 'Hubo un cambio en la configuración de tu pedido. Por favor revisa los totales nuevamente.']);
        }
    }
}