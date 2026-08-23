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
                'id'                     => (string) Str::uuid7(),
                'phone'                  => $dto->phone,
                'email'                  => $dto->email,
                'password'               => Hash::make($dto->password),
                'country_code'           => $dto->countryCode,
                'branch_id'              => $dto->branchId, // Puede ser null si está fuera de cobertura
                'is_active'              => true,
                'deleted_epoch'          => 0,
                'was_previously_deleted' => false,
                'needs_password_change'  => false,
                'idempotency_key'        => $cleanedKey,
            ]);

            // Se inyecta ID explícito y deleted_epoch conforme al nuevo diseño de tablas
            $customer->profile()->create([
                'id'            => (string) Str::uuid7(),
                'first_name'    => mb_convert_encoding(mb_strtoupper($dto->firstName), 'UTF-8'),
                'last_name'     => mb_convert_encoding(mb_strtoupper($dto->lastName), 'UTF-8'),
                'avatar_type'   => $dto->avatarType,
                'avatar_source' => $dto->avatarSource,
                'deleted_epoch' => 0,
            ]);

            CustomerAddress::query()->create([
                'id'            => (string) Str::uuid7(),
                'customer_id'   => (string) $customer->id,
                'branch_id'     => $dto->branchId, // Almacena null si no intersecta con ninguna sucursal
                'alias'         => mb_strtoupper($dto->alias),
                'address'       => mb_strtoupper($dto->address),
                'reference'     => $dto->details !== null ? mb_strtoupper($dto->details) : null,
                'position'      => [
                    'lat' => $dto->latitude,
                    'lng' => $dto->longitude,
                ],
                'is_default'    => true,
                'deleted_epoch' => 0,
            ]);

            return ActionResult::success($customer);
        });
    }
}