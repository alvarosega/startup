<?php

namespace App\Observers;

use App\Models\RemovalRequest;
use Illuminate\Support\Str;

class RemovalRequestObserver
{
    public function creating(RemovalRequest $request): void
    {
        $request->code = 'REM-' . now()->format('ym') . '-' . Str::upper(Str::random(4));
    }
}