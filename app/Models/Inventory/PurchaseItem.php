<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'purchase_id', 'sku_id', 'quantity', 'cost_price'];

    protected $casts = [
        'id'         => 'string',
        'quantity'   => 'float',
        'cost_price' => 'float',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class);
    }
}