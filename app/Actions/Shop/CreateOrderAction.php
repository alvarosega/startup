<?php

namespace App\Actions\Shop;

use App\DTOs\Shop\CreateOrderDTO;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\UserAddress;
use App\Models\InventoryLot;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class CreateOrderAction
{
    public function execute(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            
            // 1. Recuperar Carrito (Bloqueo pesimista para evitar cambios de último segundo)
            // Es vital filtrar por branch_id para asegurar que compramos el stock correcto.
            $cart = Cart::where('user_id', $dto->user->id)
                ->where('branch_id', $dto->branchId)
                ->with('items.sku')
                ->lockForUpdate() 
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw new Exception("El carrito está vacío, ha expirado o cambiaste de sucursal.");
            }

            // 2. Preparar Datos de Entrega (Delivery vs Pickup)
            if ($dto->deliveryType === 'delivery') {
                // A. CASO DELIVERY
                $address = UserAddress::where('id', $dto->addressId)
                    ->where('user_id', $dto->user->id)
                    ->firstOrFail();

                // VALIDACIÓN DE SEGURIDAD CRÍTICA:
                // La dirección de entrega debe pertenecer a la misma sucursal del carrito.
                if ($address->branch_id !== $dto->branchId) {
                    throw new Exception("La dirección seleccionada pertenece a otra zona. Por favor selecciona una dirección válida para esta sucursal.");
                }

                $deliveryData = [
                    'type' => 'delivery',
                    'alias' => $address->alias,
                    'address' => $address->address,
                    'details' => $address->details ?? '',
                    'coordinates' => ['lat' => $address->latitude, 'lng' => $address->longitude],
                    'contact_phone' => $dto->user->phone
                ];

            } else {
                // B. CASO PICKUP (Recojo en Tienda)
                $branch = Branch::findOrFail($dto->branchId);
                
                $deliveryData = [
                    'type' => 'pickup',
                    'alias' => 'RECOJO EN TIENDA',
                    'address' => $branch->address ?? 'Dirección de la sucursal',
                    'details' => $branch->name,
                    'coordinates' => ['lat' => $branch->latitude, 'lng' => $branch->longitude],
                    'contact_phone' => $dto->user->phone
                ];
            }

            // 3. Crear Cabecera de Orden (Estado: pending_proof)
            $order = Order::create([
                'code' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $dto->user->id,
                'branch_id' => $dto->branchId,
                'status' => 'pending_proof',
                'delivery_type' => $dto->deliveryType,
                'reservation_expires_at' => Carbon::now()->addMinutes(5), // 5 Minutos para pagar
                'total_amount' => 0, // Se calcula abajo
                'delivery_data' => $deliveryData
            ]);

            $totalOrder = 0;

            // 4. PROCESAR ITEMS Y RESERVAR STOCK
            foreach ($cart->items as $cartItem) {
                
                $qtyNeeded = $cartItem->quantity;
                $currentPrice = $cartItem->sku->getCurrentPrice($dto->branchId);

                // A. Buscar lotes disponibles (FIFO)
                // Solo lotes de ESTA sucursal que tengan disponibilidad real (Físico - Reservado > 0)
                $lots = InventoryLot::where('branch_id', $dto->branchId)
                    ->where('sku_id', $cartItem->sku_id)
                    ->whereRaw('(quantity - reserved_quantity) > 0')
                    ->orderBy('created_at', 'asc') // Vender lo más antiguo primero
                    ->lockForUpdate() // Bloqueo crítico de DB
                    ->get();

                // B. Verificar disponibilidad total
                $totalAvailable = $lots->sum(fn($lot) => $lot->quantity - $lot->reserved_quantity);

                if ($totalAvailable < $qtyNeeded) {
                    throw new Exception("Stock insuficiente para '{$cartItem->sku->name}'. Disponible: {$totalAvailable}");
                }

                // C. Reservar en los lotes (Incrementar reserved_quantity)
                foreach ($lots as $lot) {
                    if ($qtyNeeded <= 0) break;

                    // Cuánto podemos sacar de este lote específico
                    $availableInLot = $lot->quantity - $lot->reserved_quantity;
                    $take = min($availableInLot, $qtyNeeded);

                    // AUMENTAMOS LA RESERVA (No tocamos 'quantity' todavía)
                    $lot->increment('reserved_quantity', $take);
                    
                    $qtyNeeded -= $take;
                }

                // D. Guardar Item en Orden (Snapshot de precio)
                OrderItem::create([
                    'order_id' => $order->id,
                    'sku_id' => $cartItem->sku_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $currentPrice,
                    'subtotal' => $currentPrice * $cartItem->quantity
                ]);

                $totalOrder += ($currentPrice * $cartItem->quantity);
            }

            // 5. Finalizar Orden
            $order->update(['total_amount' => $totalOrder]);

            // 6. Eliminar Carrito
            $cart->delete();

            return $order;
        });
    }
}