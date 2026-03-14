<?php

namespace App\Actions\Customer\Order;

use App\Models\Order;
use App\DTOs\Customer\Order\UploadOrderProofDTO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;

class UploadOrderProofAction
{
    /**
     * Procesa la subida del comprobante, valida el tiempo y limpia archivos antiguos.
     * Blindado contra Race Conditions (Doble Clic).
     */
    public function execute(UploadOrderProofDTO $dto): void
    {
        // TODO ocurre dentro de la transacción para garantizar atomicidad absoluta
        DB::transaction(function () use ($dto) {
            
            // 1. Recuperar orden con BLOQUEO a nivel de fila (Pessimistic Locking)
            $order = Order::where('id', $dto->orderId)
                ->where('customer_id', $dto->customerId)
                ->lockForUpdate() // Nadie más puede leer/modificar esta fila hasta que el commit termine
                ->firstOrFail();

            // 2. Validaciones de Negocio Críticas
            if ($order->status !== 'pending_payment') {
                throw new Exception('Esta orden ya no permite la subida de comprobantes.');
            }

            if ($order->reservation_expires_at && $order->reservation_expires_at->isPast()) {
                $order->update(['status' => 'expired']);
                throw new Exception('El tiempo de reserva ha expirado. El stock fue liberado.');
            }

            // 3. Gestión de Archivos (Zero-Waste)
            if ($order->proof_of_payment) {
                Storage::disk('public')->delete($order->proof_of_payment);
            }

            $extension = $dto->proofFile->getClientOriginalExtension();
            $filename = 'proofs/' . $order->code . '_' . Str::random(12) . '.' . $extension;
            
            // Guardar nuevo archivo
            $path = $dto->proofFile->storeAs('', $filename, 'public');

            // 4. Actualización de Estado Final
            $order->update([
                'proof_of_payment' => $path,
                'status'           => 'under_review',
                'rejection_reason' => null, // Limpiamos razón de rechazo anterior si existía
            ]);
            
        });
    }
}