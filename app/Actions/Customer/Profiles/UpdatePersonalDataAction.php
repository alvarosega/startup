<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\Architecture\ActionResult;
use App\DTOs\Customer\Profiles\ProfileUpdateData;
use App\Models\Users\Customer;
use Illuminate\Support\Facades\DB;

final class UpdatePersonalDataAction
{
    public function execute(Customer $customer, ProfileUpdateData $dto): ActionResult
    {
        DB::transaction(function () use ($customer, $dto): void {
            $customer->profile()->update([
                'first_name' => mb_convert_encoding($dto->firstName, 'UTF-8'),
                'last_name' => mb_convert_encoding($dto->lastName, 'UTF-8'),
                'birth_date' => $dto->birthDate,
                'gender' => $dto->gender,
            ]);
        });

        return ActionResult::success();
    }
}