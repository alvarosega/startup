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

        if ($order->status !== 'pending_payment') {
            throw new Exception('El comprobante ya fue recibido o la orden expiró.');
        }

        $filename = 'proof_' . $order->code . '_' . Str::random(8) . '.' . $extension;
        $path = $dto->proofFile->storeAs('proofs', $filename, 'public');

        $order->update([
            'proof_of_payment' => $path,
            'status' => 'under_review',
        ]);
    }
}