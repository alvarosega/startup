<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Driver;

use App\Models\Users\Driver;

class SearchDeletedDriverAction
{
    public function execute(string $phone): ?Driver
    {
        return Driver::onlyTrashed()
            ->with(['profile', 'branch'])
            ->where('phone', $phone)
            ->first();
    }
}