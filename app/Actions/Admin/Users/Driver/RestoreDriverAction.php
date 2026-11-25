
<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Driver;

use App\Models\Users\Driver;
use App\DTOs\Admin\Users\AuditContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RestoreDriverAction
{
    public function execute(string $id, AuditContext $context): void
    {
        $driver = Driver::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($driver, $context) {
            $driver->restore();

            $driver->update([
                'deleted_epoch'          => 0,
                'status'                 => 'pending',
                'was_previously_deleted' => true,
                'is_online'              => false,
                'is_available'           => false,
            ]);

            if (Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')->insert([
                    'id'             => (string) Str::uuid7(),
                    'causer_type'    => $context->causerType,
                    'causer_id'      => $context->causerId,
                    'target_type'    => Driver::class,
                    'target_id'      => $driver->id,
                    'action'         => 'driver_restored_by_admin',
                    'payload_before' => json_encode(['deleted_at' => $driver->deleted_at]),
                    'payload_after'  => json_encode(['status' => 'pending', 'was_previously_deleted' => true]),
                    'ip_address'     => $context->ipAddress,
                    'user_agent'     => $context->userAgent,
                    'created_at'     => now(),
                ]);
            }
        });
    }
}
