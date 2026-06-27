<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'purchase_id', 'sku_id', 'quantity', 'cost_price', 'deleted_epoch'];

    protected $casts = [
        'quantity' => 'float',
        'cost_price' => 'float',
        'deleted_epoch' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $model) {
            if ($model->purchase->status === 'COMPLETED') {
                throw new \BadMethodCallException('VIOLACIÓN_CONTABLE: Los ítems de una compra COMPLETED no pueden ser modificados.');
            }
        });

        static::deleting(function (self $model) {
            if ($model->purchase->status === 'COMPLETED') {
                throw new \BadMethodCallException('VIOLACIÓN_CONTABLE: Los ítems de una compra COMPLETED no pueden ser removidos.');
            }
            $model->deleted_epoch = time();
            $model->saveQuietly();
        });
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class);
    }
}