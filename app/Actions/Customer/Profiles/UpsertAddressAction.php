<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\Architecture\ActionResult;
use App\DTOs\Customer\Profiles\AddressData;
use App\Models\Users\Customer;
use App\Services\Geo\BranchCoverageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class UpsertAddressAction
{
    public function __construct(protected BranchCoverageService $geoService) {}

    public function execute(Customer $customer, AddressData $dto, ?string $addressId = null): ActionResult
    {
        DB::transaction(function () use ($customer, $dto, $addressId): void {
            $assignedBranchId = $dto->branchId ?? $this->geoService->identifyBranch($dto->latitude, $dto->longitude);
            
            $isFirstAddress = !$customer->addresses()->where('deleted_epoch', 0)->exists();
            $shouldBeDefault = $isFirstAddress;

            if ($addressId) {
                $address = $customer->addresses()->where('deleted_epoch', 0)->findOrFail($addressId);
                $shouldBeDefault = (bool) $address->is_default;

                DB::table('customer_addresses')
                    ->where('id', $addressId)
                    ->update([
                        'branch_id' => $assignedBranchId,
                        'alias' => $dto->alias,
                        'address' => $dto->address,
                        'reference' => $dto->details,
                        'position' => DB::raw("ST_GeomFromText('POINT({$dto->longitude} {$dto->latitude})')"),
                        'updated_at' => now(),
                    ]);
            } else {
                $addressId = (string) Str::uuid7();
                DB::table('customer_addresses')->insert([
                    'id' => $addressId,
                    'customer_id' => (string) $customer->id,
                    'branch_id' => $assignedBranchId,
                    'alias' => $dto->alias,
                    'address' => $dto->address,
                    'reference' => $dto->details,
                    'position' => DB::raw("ST_GeomFromText('POINT({$dto->longitude} {$dto->latitude})')"),
                    'is_default' => $shouldBeDefault,
                    'deleted_epoch' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($shouldBeDefault) {
                DB::table('customer_addresses')
                    ->where('customer_id', $customer->id)
                    ->where('id', '!=', $addressId)
                    ->update(['is_default' => false, 'updated_at' => now()]);

                $customer->update([
                    'branch_id' => $assignedBranchId,
                    'last_known_location' => DB::raw("ST_GeomFromText('POINT({$dto->longitude} {$dto->latitude})')"),
                ]);
            }
        });

        return ActionResult::success();
    }
}