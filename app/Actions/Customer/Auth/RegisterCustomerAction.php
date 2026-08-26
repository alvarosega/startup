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
                    return ActionResult::success($duplicate);
                }
            }

            // Resolución de Branch: Asignada o Fallback a Default si está fuera de cobertura
            $assignedBranchId = $dto->branchId;
            if (!$assignedBranchId) {
                $defaultBranch = \App\Models\Operations\Branch::active()->where('is_default', true)->first()
                    ?? \App\Models\Operations\Branch::active()->first();
                $assignedBranchId = $defaultBranch?->id;
            }

            /** @var Customer $customer */
            $customer = Customer::query()->create([
                'phone'                  => $dto->phone,
                'email'                  => strtolower($dto->email),
                'password'               => $dto->password, // Sin Hash::make() manual (gestionado por cast)
                'country_code'           => $dto->countryCode ?? 'BO',
                'branch_id'              => $assignedBranchId,
                'is_active'              => true,
                'deleted_epoch'          => 0,
                'was_previously_deleted' => false,
                'needs_password_change'  => false,
                'idempotency_key'        => $cleanedKey,
            ]);

            // Opción A: customer_id es PK y FK (no se envía 'id')
            $customer->profile()->create([
                'first_name'    => mb_convert_encoding(mb_strtoupper($dto->firstName), 'UTF-8'),
                'last_name'     => mb_convert_encoding(mb_strtoupper($dto->lastName), 'UTF-8'),
                'avatar_type'   => $dto->avatarType ?? 'icon',
                'avatar_source' => $dto->avatarSource,
                'deleted_epoch' => 0,
            ]);

            CustomerAddress::query()->create([
                'customer_id'   => (string) $customer->id,
                'branch_id'     => $assignedBranchId,
                'alias'         => $dto->alias ? mb_strtoupper($dto->alias) : 'PRINCIPAL',
                'address'       => mb_strtoupper($dto->address),
                'reference'     => $dto->details !== null ? mb_strtoupper($dto->details) : null,
                'position'      => DB::raw("ST_GeomFromText('POINT({$dto->longitude} {$dto->latitude})', 4326, 'axis-order=long-lat')"),
                'is_default'    => true,
                'deleted_epoch' => 0,
            ]);

            return ActionResult::success($customer);
        });
    }
}