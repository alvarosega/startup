<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Architecture\ActionResult;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Models\Users\Customer;
use App\Models\Users\CustomerAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class RegisterCustomerAction
{
    /**
     * @param string|array<int, string>|null $idempotencyKey
     */
    public function execute(RegisterCustomerData $dto, string|array|null $idempotencyKey = null): ActionResult
    {
        $cleanedKey = is_array($idempotencyKey) ? ($idempotencyKey[0] ?? null) : $idempotencyKey;

        return DB::transaction(function () use ($dto, $cleanedKey): ActionResult {
            if ($cleanedKey !== null) {
                /** @var Customer|null $duplicate */
                $duplicate = Customer::query()
                    ->where('idempotency_key', $cleanedKey)
                    ->first();

                if ($duplicate !== null) {
                    Customer::query()
                        ->where('id', $duplicate->id)
                        ->lockForUpdate()
                        ->first();

                    return ActionResult::success($duplicate);
                }
            }

            /** @var Customer $customer */
            $customer = Customer::query()->create([
                'id' => (string) Str::uuid7(),
                'phone' => $dto->phone,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'country_code' => $dto->countryCode,
                'branch_id' => $dto->branchId,
                'is_active' => true,
                'idempotency_key' => $cleanedKey,
            ]);

            $customer->profile()->create([
                'first_name' => mb_convert_encoding($dto->firstName, 'UTF-8'),
                'last_name' => mb_convert_encoding($dto->lastName, 'UTF-8'),
                'avatar_type' => $dto->avatarType,
                'avatar_source' => $dto->avatarSource,
            ]);

            CustomerAddress::query()->create([
                'id' => (string) Str::uuid7(),
                'customer_id' => (string) $customer->id,
                'branch_id' => $dto->branchId,
                'alias' => $dto->alias,
                'address' => $dto->address,
                'reference' => $dto->details,
                'position' => [
                    'lat' => $dto->latitude,
                    'lng' => $dto->longitude,
                ],
                'is_default' => true,
            ]);
            return ActionResult::success($customer);
        });
    }
}