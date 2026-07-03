<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\Architecture\ActionResult;
use App\Models\Users\Customer;
use Illuminate\Support\Facades\DB;

final class UpdateAvatarAction
{
    public function execute(Customer $customer, string $avatarSource): ActionResult
    {
        DB::transaction(function () use ($customer, $avatarSource): void {
            $customer->profile()->update([
                'avatar_type' => 'icon',
                'avatar_source' => $avatarSource,
            ]);
        });

        return ActionResult::success();
    }
}