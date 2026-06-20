<?php

namespace App\Actions\Driver\Profile;

use App\Models\Driver;

class ToggleDriverStatusAction
{
    public function execute(Driver $driver, bool $isOnline): void
    {
        $driver->update([
            'is_online' => $isOnline,
            'last_seen_at' => now()
        ]);
    }
}