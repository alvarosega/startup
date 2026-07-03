<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateCustomerAvatarAction
{
    public function execute(string $customerId, string $avatarSource): void
    {
        // Validación perimetral interna del catálogo de avatares permitidos
        $allowedAvatars = array_map(fn(int $i) => "avatar_{$i}.png", range(1, 8));

        if (!in_array($avatarSource, $allowedAvatars, true)) {
            throw ValidationException::withMessages([
                'avatar_source' => 'El avatar seleccionado no forma parte del catálogo autorizado del sistema.',
            ]);
        }

        DB::transaction(function () use ($customerId, $avatarSource): void {
            DB::table('customer_profiles')
                ->where('customer_id', $customerId)
                ->where('deleted_epoch', 0)
                ->update([
                    'avatar_source' => $avatarSource,
                    'updated_at' => now(),
                ]);
        });
    }
}