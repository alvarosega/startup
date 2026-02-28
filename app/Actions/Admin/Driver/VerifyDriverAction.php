<?php

namespace App\Actions\Admin\Driver;

use App\DTOs\Admin\Driver\VerifyDriverData;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class VerifyDriverAction
{
    public function execute(VerifyDriverData $data, Driver $driver): void
    {
        DB::transaction(function () use ($data, $driver) {
            // Actualiza acceso de sistema
            $driver->update([
                'is_active' => $data->isActive
            ]);

            // Actualiza estado de revisión documental
            $driver->details()->update([
                'verification_status' => $data->isIdentityVerified ? 'verified' : 'pending'
            ]);
        });
    }
}