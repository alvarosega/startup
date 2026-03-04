<?php

namespace App\Actions\Driver\Profile;

use App\Models\Driver;
use App\Services\Logistics\RedisLocationService;

class ToggleDriverStatusAction
{
    public function __construct(private RedisLocationService $redis) {}

    public function execute(Driver $driver, bool $isOnline): void
    {
        $driver->update(['is_online' => $isOnline]);

        if (!$isOnline) {
            $this->redis->removeLocation((string) $driver->id);
        }
    }
}