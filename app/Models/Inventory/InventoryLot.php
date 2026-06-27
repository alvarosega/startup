<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLot extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'purchase_id', 'transfer_id', 'branch_id', 'sku_id', 'lot_code',
        'quantity', 'initial_quantity', 'safety_quantity', 'initial_safety_quantity',
        'reserved_quantity', 'cost_price', 'is_quarantine', 'expiration_date', 'deleted_epoch'
    ];

    protected $casts = [
        'quantity' => 'float',
        'initial_quantity' => 'float',
        'safety_quantity' => 'float',
        'initial_safety_quantity' => 'float',
        'reserved_quantity' => 'float',
        'cost_price' => 'float',
        'is_quarantine' => 'boolean',
        'expiration_date' => 'date',
        'deleted_epoch' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            $model->saveQuietly();
        });
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}