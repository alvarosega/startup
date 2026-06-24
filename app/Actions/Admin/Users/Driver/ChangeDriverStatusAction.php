<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Driver;

use App\Models\Users\Driver;
use App\DTOs\Admin\Users\Driver\ChangeDriverStatusDTO;
use App\DTOs\Admin\Users\AuditContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChangeDriverStatusAction
{
    public function execute(ChangeDriverStatusDTO $dto, AuditContext $context): void
    {
        $driver = Driver::with('profile')->findOrFail($dto->driverId);

        $oldState = [
            'status' => $driver->status,
            'is_online' => $driver->is_online,
            'is_available' => $driver->is_available
        ];

        $newState = [
            'status' => $dto->status,
            'is_online' => $driver->is_online,
            'is_available' => $driver->is_available
        ];

        // Lógica de desconexión forzada bajo suspensión
        if ($dto->status === 'suspended') {
            $newState['is_online'] = false;
            $newState['is_available'] = false;
        }

        DB::transaction(function () use ($driver, $dto, $oldState, $newState, $context) {
            $driver->update($newState);

            if ($dto->status === 'rejected' && !is_null($dto->rejectionReason)) {
                $driver->profile()->update([
                    'rejection_reason' => $dto->rejectionReason
                ]);
            }

            DB::table('audit_logs')->insert([
                'id' => (string) Str::uuid7(),
                'causer_type' => $context->causerType,
                'causer_id' => $context->causerId,
                'target_type' => Driver::class,
                'target_id' => $driver->id,
                'action' => "driver_status_to_{$dto->status}",
                'payload_before' => json_encode($oldState),
                'payload_after' => json_encode(array_merge($newState, ['rejection_reason' => $dto->rejectionReason])),
                'ip_address' => $context->ipAddress,
                'user_agent' => $context->userAgent,
                'created_at' => now()
            ]);
        });
    }
}