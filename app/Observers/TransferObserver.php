<?php

namespace App\Observers;

use App\Models\Transfer;
use Illuminate\Support\Str;

class TransferObserver
{
    public function creating(Transfer $transfer): void
    {
        // Genera: TRF-2601-A1B2
        $transfer->code = 'TRF-' . now()->format('ym') . '-' . Str::upper(Str::random(4));
    }
}