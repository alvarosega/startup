<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\Architecture\ActionResult;
use App\Models\Users\Customer;
use Illuminate\Support\Facades\DB;

final class SetDefaultAddressAction
{
    public function execute(Customer $customer, string $addressId): ActionResult
    {
        DB::transaction(function () use ($customer, $addressId): void {
            $address = $customer->addresses()
                ->where('deleted_epoch', 0)
                ->findOrFail($addressId);

            DB::table('customer_addresses')
                ->where('customer_id', $customer->id)
                ->update(['is_default' => false, 'updated_at' => now()]);

            DB::table('customer_addresses')
                ->where('id', $addressId)
                ->update(['is_default' => true, 'updated_at' => now()]);

            // Extracción nativa de componentes espaciales de MySQL 8.0 para la sincronización síncrona
            /** @var object $geoData */
            $geoData = DB::table('customer_addresses')
                ->selectRaw('branch_id, ST_X(position) as lng, ST_Y(position) as lat')
                ->where('id', $addressId)
                ->first();

            $customer->update([
                'branch_id' => $geoData->branch_id,
                'last_known_location' => DB::raw("ST_GeomFromText('POINT({$geoData->lng} {$geoData->lat})')"),
            ]);
        });

        return ActionResult::success();
    }
}