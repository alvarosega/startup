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
    public function index()
    {
        $user = Auth::user();
        
        $cart = Cart::where('user_id', $user->id)
            ->with(['items.sku.product', 'items.sku.prices', 'branch'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('shop.index');
        }

        // Calcular totales con precio actual
        $items = $cart->items->map(function ($item) use ($cart) {
            $price = $item->sku->getCurrentPrice($cart->branch_id);
            return [
                'sku_id' => $item->sku_id,
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
                'branch_name' => $cart->branch->name
            ],
            'addresses' => UserAddress::where('user_id', $user->id)->get(),
            // Preseleccionar dirección default
            'default_address_id' => UserAddress::where('user_id', $user->id)->where('is_default', true)->value('id')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id', // Obligatorio para delivery
        ]);

        $user = Auth::user();
        
        try {
            return DB::transaction(function () use ($user, $request) {
                
                // 1. Bloquear Carrito para evitar modificaciones concurrentes
                $cart = Cart::where('user_id', $user->id)
                    ->with('items.sku')
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($cart->items->isEmpty()) throw new \Exception("El carrito está vacío.");

                // 2. Preparar Dirección (Snapshot)
                $address = UserAddress::findOrFail($request->address_id);
                $deliveryData = [
                    'alias' => $address->alias,
                    'address' => $address->address,
                    'details' => $address->details,
                    'coordinates' => ['lat' => $address->latitude, 'lng' => $address->longitude],
                    'contact' => $user->phone
                ];

                // 3. Crear Cabecera de Orden
                $order = Order::create([
                    'code' => 'ORD-' . strtoupper(Str::random(8)),
                    'user_id' => $user->id,
                    'branch_id' => $cart->branch_id,
                    'status' => 'pending_proof',
                    'reservation_expires_at' => Carbon::now()->addMinutes(5), // VENTANA DE 5 MINUTOS
                    'total_amount' => 0, // Se calcula abajo
                    'delivery_data' => $deliveryData
                ]);

                $totalOrder = 0;

                // 4. LÓGICA DE RESERVA DE STOCK (CRÍTICO)
                foreach ($cart->items as $cartItem) {
                    
                    $qtyNeeded = $cartItem->quantity;
                    
                    // Buscar Lotes Disponibles (FIFO)
                    // Filtramos donde (cantidad_real - cantidad_reservada) > 0
                    $lots = InventoryLot::where('branch_id', $cart->branch_id)
                        ->where('sku_id', $cartItem->sku_id)
                        ->whereRaw('(quantity - reserved_quantity) > 0') 
                        ->orderBy('expiration_date', 'asc') // Primero lo que vence antes
                        ->orderBy('created_at', 'asc')
                        ->lockForUpdate()
                        ->get();

                    // Verificar disponibilidad total antes de reservar
                    $totalAvailable = $lots->sum(fn($lot) => $lot->quantity - $lot->reserved_quantity);

                    if ($totalAvailable < $qtyNeeded) {
                        throw new \Exception("Stock insuficiente para: " . $cartItem->sku->name . ". Disponible: " . $totalAvailable);
                    }

                    // Reservar en los lotes
                    foreach ($lots as $lot) {
                        if ($qtyNeeded <= 0) break;

                        $lotAvailable = $lot->quantity - $lot->reserved_quantity;
                        $take = min($lotAvailable, $qtyNeeded);

                        // Aumentamos la reserva (No tocamos el físico 'quantity' aún)
                        $lot->increment('reserved_quantity', $take);
                        
                        $qtyNeeded -= $take;
                    }

                    // Guardar Item en la Orden (Snapshot de precio)
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
                
                // 5. SOFT DELETE DEL CARRITO
                // No lo borramos permanentemente, solo lo ocultamos.
                // Si la orden expira, lo restauraremos.
                $cart->delete(); 

                return redirect()->route('orders.show', $order->code)
                    ->with('success', 'Productos reservados por 5 minutos. ¡Sube tu comprobante!');
            });

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}