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

    public function execute(UploadOrderProofDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $order = Order::where('id', $dto->orderId)
                ->where('customer_id', $dto->customerId)
                ->lockForUpdate()
                ->firstOrFail();

            // RECTIFICACIÓN: El estado correcto es 'pending'
            if ($order->status !== 'pending') { 
                throw new Exception("Esta orden (Estado: {$order->status}) no permite la subida de comprobantes.");
            }

            if ($order->reservation_expires_at && $order->reservation_expires_at->isPast()) {
                $order->update(['status' => 'expired']);
                throw new Exception('El tiempo de reserva ha expirado.');
            }

            if ($order->proof_of_payment) {
                Storage::disk('public')->delete($order->proof_of_payment);
            }

            $extension = $dto->proofFile->getClientOriginalExtension();
            $filename = 'proofs/' . $order->code . '_' . Str::random(12) . '.' . $extension;
            
            // El disco 'public' mapea a storage/app/public
            $path = $dto->proofFile->storeAs('', $filename, 'public');

            $order->update([
                'proof_of_payment' => $path,
                'status'           => 'payment_pending', // RECTIFICADO: Coincide con la migración
                'rejection_reason' => null,
            ]);
        });
    }
}