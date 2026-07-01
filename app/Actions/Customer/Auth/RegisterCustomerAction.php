<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Architecture\ActionResult;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Models\Users\Customer;
use App\Services\Cart\CartService;
use App\Services\Geo\BranchCoverageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class RegisterCustomerAction
{
    public function __construct(
        protected BranchCoverageService $geoService,
        protected CartService $cartService
    ) {}

    public function execute(RegisterCustomerData $dto, ?string $idempotencyKey = null): ActionResult
    {
        return DB::transaction(function () use ($dto, $idempotencyKey): ActionResult {
            if ($idempotencyKey) {
                /** @var Customer|null $duplicate */
                $duplicate = Customer::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($duplicate) {
                    return ActionResult::success($duplicate);
                }
            }

            $assignedBranchId = $dto->branchId ?? $this->geoService->identifyBranch($dto->latitude, $dto->longitude);

            /** @var Customer $customer */
            $customer = Customer::query()->create([
                'id' => (string) Str::uuid7(),
                'phone' => $dto->phone,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'country_code' => $dto->countryCode,
                'branch_id' => $assignedBranchId,
                'last_known_location' => DB::raw("ST_GeomFromText('POINT({$dto->longitude} {$dto->latitude})')"),
                'is_active' => true,
                'idempotency_key' => $idempotencyKey,
            ]);

            $customer->profile()->create([
                'first_name' => mb_convert_encoding($dto->firstName, 'UTF-8'),
                'last_name' => mb_convert_encoding($dto->lastName, 'UTF-8'),
                'avatar_type' => $dto->avatarType,
                'avatar_source' => $dto->avatarSource,
            ]);

            DB::table('customer_addresses')->insert([
                'id' => (string) Str::uuid7(),
                'customer_id' => (string) $customer->id,
                'branch_id' => $assignedBranchId,
                'alias' => $dto->alias,
                'address' => $dto->address,
                'reference' => $dto->details,
                'position' => DB::raw("ST_GeomFromText('POINT({$dto->longitude} {$dto->latitude})')"),
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($dto->guestUuid) {
                $this->cartService->fusionGuestCart((string) $customer->id, $dto->guestUuid);
            }

            return ActionResult::success($customer);
        });
    }
}