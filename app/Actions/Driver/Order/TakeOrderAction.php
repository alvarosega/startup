<?php

namespace App\Actions\Driver\Order;

use App\Models\Driver;
use App\Models\Order;
use Exception;

class TakeOrderAction
{
    public function execute(string $orderId, Driver $driver): void
    {
        $otp = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $affectedRows = Order::where('id', $orderId)
            ->where('branch_id', $driver->branch_id)
            ->where('status', 'preparing')
            ->whereNull('driver_id')
            ->update([
                'driver_id' => $driver->id,
                'delivery_otp' => $otp
            ]);

        if ($affectedRows === 0) {
            throw new Exception('Objetivo no disponible. Es probable que otro conductor lo haya tomado.');
        }
    }
}