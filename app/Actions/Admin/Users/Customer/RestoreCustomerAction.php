<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Customer;

use App\Models\Users\Customer;
use App\DTOs\Admin\Users\AuditContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestoreCustomerAction
{
    public function execute(string $id, AuditContext $context): void
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($customer, $context) {
            $customer->restore();
            
            $customer->update([
                'deleted_epoch' => 0,
                'is_active' => false,
                'was_previously_deleted' => true
            ]);

            DB::table('audit_logs')->insert([
                'id' => (string) Str::uuid7(),
                'causer_type' => $context->causerType,
                'causerId' => $context->causerId,
                'target_type' => Customer::class,
                'target_id' => $customer->id,
                'action' => 'customer_restored_by_admin',
                'payload_before' => json_encode(['deleted_at' => $customer->deleted_at]),
                'payload_after' => json_encode(['is_active' => false, 'was_previously_deleted' => true]),
                'ip_address' => $context->ipAddress,
                'user_agent' => $context->userAgent,
                'created_at' => now()
            ]);
        });
    }
}