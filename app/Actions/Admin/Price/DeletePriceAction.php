<?php

declare(strict_types=1);

namespace App\Actions\Admin\Price;

use App\Models\Price;
use Illuminate\Support\Facades\DB;

class DeletePriceAction
{
    public function execute(Price $price, string $adminId): void
    {
        DB::transaction(function () use ($price, $adminId) {
            $lockedPrice = Price::where('id', $price->id)->lockForUpdate()->firstOrFail();
            
            $lockedPrice->update(['updated_by_id' => $adminId]);
            $lockedPrice->delete();
        });
    }
}