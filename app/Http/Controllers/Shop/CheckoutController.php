<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryLot;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    /**
     * Paso 1: Resumen y Selección de Dirección
     */
    public function index()
    {
        $user = Auth::user();
        
        // Cargar carrito con precios actualizados
        $cart = Cart::where('user_id', $user->id)
            ->with(['items.sku.product', 'items.sku.prices', 'branch'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('shop.index');
        }

        // Calcular totales
        $items = $cart->items->map(function ($item) use ($cart) {
            $price = $item->sku->getCurrentPrice($cart->branch_id);
            return [
                'sku_id' => $item->sku_id,
                'name' => $item->sku->name,
                'quantity' => $item->quantity,
                'unit_price' => $price,
                'subtotal' => $price * $item->quantity
            ];
        });

        return Inertia::render('Shop/Checkout/Index', [
            'cart' => [
                'items' => $items,
                'total' => $items->sum('subtotal'),
                'branch_name' => $cart->branch->name
            ],
            'addresses' => UserAddress::where('user_id', $user->id)->get(),
            'default_address' => UserAddress::where('user_id', $user->id)->where('is_default', true)->first()
        ]);
    }

    /**
     * Paso 2: CREACIÓN DE LA ORDEN (Reserva de Stock)
     */
    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'nullable|exists:user_addresses,id',
        ]);

        $user = Auth::user();
        
        try {
            return DB::transaction(function () use ($user, $request) {
                
                // 1. Recuperar Carrito Bloqueado
                $cart = Cart::where('user_id', $user->id)
                    ->with('items.sku')
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($cart->items->isEmpty()) throw new \Exception("El carrito está vacío.");

                // 2. Preparar Datos de Entrega
                $address = UserAddress::find($request->address_id);
                $deliveryData = [
                    'address' => $address ? $address->address : 'Retiro en Tienda',
                    'coordinates' => $address ? ['lat' => $address->latitude, 'lng' => $address->longitude] : null,
                    'contact' => $user->phone
                ];

                // 3. Crear Orden
                $order = Order::create([
                    'code' => 'ORD-' . strtoupper(Str::random(8)),
                    'user_id' => $user->id,
                    'branch_id' => $cart->branch_id,
                    'status' => 'pending_proof',
                    'reservation_expires_at' => Carbon::now()->addMinutes(5),
                    'total_amount' => 0,
                    'delivery_data' => $deliveryData
                ]);

                $totalOrder = 0;

                // 4. Procesar Stock (FIFO)
                foreach ($cart->items as $cartItem) {
                    
                    $requiredQty = $cartItem->quantity;
                    
                    // Verificar Stock Total
                    $availableStock = InventoryLot::where('branch_id', $cart->branch_id)
                        ->where('sku_id', $cartItem->sku_id)
                        ->sum('quantity');

                    if ($availableStock < $requiredQty) {
                        throw new \Exception("Stock insuficiente para: " . $cartItem->sku->name);
                    }

                    // Descontar de Lotes
                    $lots = InventoryLot::where('branch_id', $cart->branch_id)
                        ->where('sku_id', $cartItem->sku_id)
                        ->where('quantity', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->lockForUpdate()
                        ->get();

                    $qtyToDeduct = $requiredQty;

                    foreach ($lots as $lot) {
                        if ($qtyToDeduct <= 0) break;

                        if ($lot->quantity >= $qtyToDeduct) {
                            $lot->decrement('quantity', $qtyToDeduct);
                            $qtyToDeduct = 0;
                        } else {
                            $qtyToDeduct -= $lot->quantity;
                            $lot->update(['quantity' => 0]);
                        }
                    }

                    // Guardar Item Orden
                    $currentPrice = $cartItem->sku->getCurrentPrice($cart->branch_id);
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'sku_id' => $cartItem->sku_id,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $currentPrice,
                        'subtotal' => $currentPrice * $cartItem->quantity
                    ]);

                    $totalOrder += ($currentPrice * $cartItem->quantity);
                }

                $order->update(['total_amount' => $totalOrder]);
                
                // Vaciar Carrito
                $cart->items()->delete(); 
                $cart->delete();

                return redirect()->route('orders.show', $order->code)
                    ->with('success', 'Stock reservado. ¡Paga ahora!');
            });

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}