<?php

namespace App\Actions\Customer\Order;

use App\Models\Order;
use App\DTOs\Customer\Order\UploadOrderProofDTO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class UploadOrderProofAction
{
    public function execute(UploadOrderProofDTO $dto): void
    {
        $order = Order::where('id', $dto->orderId)
            ->where('customer_id', $dto->customerId)
            ->firstOrFail();

        $extension = $dto->proofFile->getClientOriginalExtension();

        // ---------------------------------------------------------
        // ESCENARIO A: SUBIDA DEL PRIMER PAGO (TOTAL O ANTICIPO)
        // ---------------------------------------------------------
        if ($dto->type === 'advance') {
            if (!in_array($order->advance_status, ['pending', 'rejected'])) {
                throw new Exception('El comprobante principal ya fue recibido o está en proceso.');
            }

            $filename = 'advance_' . $order->code . '_' . Str::random(8) . '.' . $extension;
            $path = $dto->proofFile->storeAs('proofs/advances', $filename, 'public');

            // Actualizamos SOLO el ledger del anticipo
            $order->update([
                'advance_proof' => $path,
                'advance_status' => 'under_review',
                // Nota: El 'status' logístico principal ('pending_payment') NO cambia aquí.
                // Cambiará a 'preparing' cuando el Admin apruebe este pago.
            ]);
        } 
        // ---------------------------------------------------------
        // ESCENARIO B: SUBIDA DEL SEGUNDO PAGO (SALDO EN RUTA)
        // ---------------------------------------------------------
        else {
            if ($order->payment_type !== 'partial') {
                throw new Exception('Esta orden es de pago total, no requiere comprobante de saldo.');
            }

            if (!in_array($order->balance_status, ['none', 'pending', 'rejected'])) {
                throw new Exception('El comprobante de saldo ya fue recibido o está en proceso.');
            }

            $filename = 'balance_' . $order->code . '_' . Str::random(8) . '.' . $extension;
            $path = $dto->proofFile->storeAs('proofs/balances', $filename, 'public');

            // Actualizamos SOLO el ledger del saldo
            $order->update([
                'balance_proof' => $path,
                'balance_status' => 'under_review',
            ]);
        }
    }
}