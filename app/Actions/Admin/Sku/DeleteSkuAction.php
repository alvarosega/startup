<?php

namespace App\Actions\Admin\Sku;

use App\Models\Sku;

class DeleteSkuAction
{
    public function execute(Sku $sku): void
    {
        // El SoftDelete del modelo se encarga de deleted_at
        $sku->delete();
    }
}