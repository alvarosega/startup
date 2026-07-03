<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\Architecture\ActionResult;
use App\Models\Users\Customer;
use Illuminate\Support\Facades\DB;

final class DeleteAddressAction
{
    public function execute(Customer $customer, string $addressId): ActionResult
    {
        return DB::transaction(function () use ($customer, $addressId): ActionResult {
            $address = $customer->addresses()
                ->where('deleted_epoch', 0)
                ->findOrFail($addressId);

            if ($address->is_default) {
                return ActionResult::failure('Restricción de Seguridad: No puede eliminar su ubicación predeterminada activa.');
            }

            $timestamp = time();

            DB::table('customer_addresses')
                ->where('id', $addressId)
                ->update([
                    'deleted_epoch' => $timestamp,
                    'updated_at' => now()
                ]);

            DB::table('audit_logs')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid7(),
                'target_type' => 'App\Models\Users\CustomerAddress',
                'target_id' => $addressId,
                'action' => 'eliminacion',
                'payload_before' => json_encode(['customer_id' => $customer->id, 'rejection_reason' => 'eliminacion']),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return ActionResult::success();
        });
    }
}