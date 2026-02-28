<?php

namespace App\Actions\Admin\Price;

use App\Models\Price;

class DeletePriceAction
{
    public function execute(string $priceId): void
    {
        // Al usar el trait SoftDeletes en el modelo, esto llenará 'deleted_at'
        Price::findOrFail($priceId)->delete();
    }
}