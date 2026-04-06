<?php

declare(strict_types=1);

namespace App\Actions\Driver\Order;

use App\Models\{Driver, Order};
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class TakeOrderWithOtpAction
{
    public function execute(string $orderId, Driver $driver, string $otp): void
    {
        DB::transaction(function () use ($orderId, $driver, $otp) {
            $order = Order::where('id', $orderId)
                ->where('branch_id', $driver->branch_id)
                ->where('status', 'ready_for_dispatch')
                ->whereNull('driver_id')
                ->lockForUpdate()
                ->first();

            if (!$order) {
                throw ValidationException::withMessages(['otp' => 'Objetivo no disponible o ya asignado.']);
            }

            if ($order->pickup_otp !== strtoupper($otp)) {
                throw ValidationException::withMessages(['otp' => 'Código de despacho incorrecto.']);
            }

            $order->update([
                'driver_id' => $driver->id,
                'status'    => 'dispatched',
            ]);
        });
    }
}