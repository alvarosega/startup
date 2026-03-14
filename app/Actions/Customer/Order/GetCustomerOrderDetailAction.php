<?php

namespace App\Actions\Customer\Order;

use App\Models\Order;
use App\DTOs\Customer\Order\GetCustomerOrderDTO;
use Illuminate\Support\Carbon;

class GetCustomerOrderDetailAction
{
    /**
     * Recupera el detalle de la orden con integridad histórica y contexto de pago.
     */
    public function execute(GetCustomerOrderDTO $dto): array
    {
        // 1. Carga con Snapshots (Relación items es vital)
        $order = Order::where('id', $dto->orderId)
            ->where('customer_id', $dto->customerId)
            ->with(['items', 'branch:id,name,address']) // No cargamos SKU/Product para usar el Snapshot
            ->firstOrFail();

        // 2. Motor de Tiempo (Sincronía Servidor-Cliente)
        $now = Carbon::now();
        $expiresAt = $order->reservation_expires_at;
        
        $secondsRemaining = $expiresAt ? max(0, $expiresAt->diffInSeconds($now, false) * -1) : 0;
        
        // Un pedido está expirado si el tiempo se acabó Y sigue en pending_payment
        $isExpired = ($order->status === 'pending_payment' && $secondsRemaining <= 0) || $order->status === 'expired';

        return [
            // Filtramos la salida para no exponer la DB cruda
            'order' => [
                'id'             => $order->id,
                'code'           => $order->code,
                'status'         => $order->status,
                'delivery_type'  => $order->delivery_type,
                'delivery_data'  => $order->delivery_data,
                'items_subtotal' => (float) $order->items_subtotal,
                'delivery_fee'   => (float) $order->delivery_fee,
                'service_fee'    => (float) $order->service_fee,
                'total_amount'   => (float) $order->total_amount,
                'payment_method' => $order->payment_method,
                'created_at'     => $order->created_at->toDateTimeString(),
                'items'          => $order->items->map(fn($item) => [
                    'name'     => $item->product_name . ' ' . $item->sku_name,
                    'image'    => $item->image_snapshot,
                    'quantity' => $item->quantity,
                    'price'    => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ]),
                'branch' => $order->branch
            ],

            // Lógica de Entrega (OTP visible solo en el momento justo)
            'delivery_otp' => $order->status === 'arrived' ? $order->delivery_otp : null,

            // Contexto de Pago QR
            'payment_context' => [
                'seconds_remaining' => $secondsRemaining,
                'is_expired'        => $isExpired,
                // Aquí inyectarás la data para el componente QR (Ej: SimplePay o link estático)
                'qr_payload'        => $isExpired ? null : $this->generateQrPayload($order),
                'bank_name'         => 'BANCO UNIÓN / BCP', 
            ]
        ];
    }

    /**
     * Placeholder para la lógica de generación de QR (Silo de Finanzas)
     */
    private function generateQrPayload(Order $order): string
    {
        // En el futuro, aquí llamarás a un Service de Pasarela (Sintesis, Libélula, etc.)
        // Por ahora devolvemos un string que identifique el pedido
        return "PAY-ORDER-{$order->code}-AMOUNT-{$order->total_amount}";
    }
}