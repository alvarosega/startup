<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Customer;

use App\Models\Users\Customer;
use App\DTOs\Admin\Users\Customer\StoreCustomerDTO;
use App\DTOs\Admin\Users\AuditContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StoreCustomerAction
{
    public function execute(StoreCustomerDTO $dto, AuditContext $context): Customer
    {
        return DB::transaction(function () use ($dto, $context) {
            $customer = Customer::create([
                'branch_id' => $dto->branchId,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'password' => Hash::make('password'), // Contraseña genérica obligatoria
                'is_active' => $dto->isActive,
                'needs_password_change' => true, // Flag de advertencia activo
                'was_previously_deleted' => false
            ]);

            $customer->profile()->create([
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName
            ]);

            DB::table('audit_logs')->insert([
                'id' => (string) Str::uuid7(),
                'causer_type' => $context->causerType,
                'causer_id' => $context->causerId,
                'target_type' => Customer::class,
                'target_id' => $customer->id,
                'action' => 'customer_created_by_admin',
                'payload_before' => null,
                'payload_after' => json_encode(['email' => $dto->email, 'is_active' => $dto->isActive]),
                'ip_address' => $context->ipAddress,
                'user_agent' => $context->userAgent,
                'created_at' => now()
            ]);

            return $customer;
        });
    }
}