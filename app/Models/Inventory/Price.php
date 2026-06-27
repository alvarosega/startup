<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Price extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'sku_id', 'branch_id', 'type', 'list_price', 'final_price',
        'min_quantity', 'priority', 'deleted_epoch', 'valid_from', 'valid_to',
        'created_by_id', 'updated_by_id'
    ];

    protected $casts = [
        'list_price'    => 'decimal:2',
        'final_price'   => 'decimal:2',
        'min_quantity'  => 'integer',
        'priority'      => 'integer',
        'deleted_epoch' => 'integer',
        'valid_from'    => 'datetime',
        'valid_to'      => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            $model->saveQuietly();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\Admin::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\Admin::class, 'updated_by_id');
    }

    /**
     * Scope para determinar el precio ganador (Winning Price) según prioridad ascendente y volumen.
     */
    public function scopeScopeWinningPrice(Builder $query, string $branchId, string $skuId, int $quantity): void
    {
        $now = now()->toDateTimeString();

        $query->where('branch_id', $branchId)
            ->where('sku_id', $skuId)
            ->where('min_quantity', '<=', $quantity)
            ->where('deleted_epoch', 0)
            ->where('valid_from', '<=', $now)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('valid_to')
                  ->orWhere('valid_to', '>=', $now);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('final_price', 'asc');
    }
}