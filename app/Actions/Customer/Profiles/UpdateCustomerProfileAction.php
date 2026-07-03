<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\DTOs\Customer\Profiles\UpdateProfileData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class UpdateCustomerProfileAction
{
    public function execute(string $customerId, UpdateProfileData $dto): void
    {
        DB::transaction(function () use ($customerId, $dto): void {
            
            // Obtener el registro de perfil activo actual
            /** @var array<string, mixed>|null $currentProfile */
            $currentProfile = DB::table('customer_profiles')
                ->where('customer_id', $customerId)
                ->where('deleted_epoch', 0)
                ->first();

            $currentTimestamp = time();

            if ($currentProfile !== null) {
                // Baja lógica del estado anterior
                DB::table('customer_profiles')
                    ->where('id', $currentProfile['id'])
                    ->update([
                        'deleted_epoch' => $currentTimestamp,
                        'updated_at' => now(),
                    ]);
            }

            // Inserción del nuevo estado inmutable
            DB::table('customer_profiles')->insert([
                'id'            => (string) Str::uuid7(),
                'customer_id'   => $customerId,
                'first_name'    => mb_convert_encoding($dto->firstName, 'UTF-8'),
                'last_name'     => mb_convert_encoding($dto->lastName, 'UTF-8'),
                'birth_date'    => $dto->birthDate,
                'gender'        => $dto->gender,
                'avatar_type'   => $currentProfile['avatar_type'] ?? 'icon',
                'avatar_source' => $currentProfile['avatar_source'] ?? 'avatar_1.svg',
                'deleted_epoch' => 0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });
    }
}