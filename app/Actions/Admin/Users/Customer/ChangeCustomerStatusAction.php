<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Customer;

use App\Models\Users\Customer;
use App\DTOs\Admin\Users\Customer\ChangeCustomerStatusDTO;
use App\DTOs\Admin\Users\AuditContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChangeCustomerStatusAction
{
    public function execute(ChangeCustomerStatusDTO $dto, AuditContext $context): void
    {
        $customer = Customer::findOrFail($dto->customerId);
        
        $oldState = ['is_active' => $customer->is_active];
        $newState = ['is_active' => $dto->isActive];

        DB::transaction(function () use ($customer, $oldState, $newState, $context) {
            $customer->update($newState);

            DB::table('audit_logs')->insert([
                'id' => (string) Str::uuid7(),
                'causer_type' => $context->causerType,
                'causer_id' => $context->causerId,
                'target_type' => Customer::class,
                'target_id' => $customer->id,
                'action' => 'customer_status_mutated',
                'payload_before' => json_encode($oldState),
                'payload_after' => json_encode($newState),
                'ip_address' => $context->ipAddress,
                'user_agent' => $context->userAgent,
                'created_at' => now()
            ]);
        });
    }
}