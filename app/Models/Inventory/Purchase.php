<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'branch_id', 'provider_id', 'admin_id', 'document_number',
        'purchase_date', 'payment_type', 'status', 'deleted_epoch'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'deleted_epoch' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $model) {
            if ($model->getOriginal('status') === 'COMPLETED') {
                throw new \BadMethodCallException('VIOLACIÓN_CONTABLE: Una compra consolidada como COMPLETED es inmutable.');
            }
        });

        static::deleting(function (self $model) {
            if ($model->getOriginal('status') === 'COMPLETED') {
                throw new \BadMethodCallException('VIOLACIÓN_CONTABLE: Prohibido aplicar Soft Delete a una compra COMPLETED.');
            }
            $model->deleted_epoch = time();
            $model->saveQuietly();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Provider::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\Admin::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}