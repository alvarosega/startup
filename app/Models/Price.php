<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Price extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sku_id', 'branch_id', 'type', 'list_price', 
        'final_price', 'min_quantity', 'priority', 'valid_from', 'valid_to',
        'created_by_id', 'updated_by_id', 'deleted_epoch'
    ];

    protected $casts = [
        'valid_from'    => 'datetime',
        'valid_to'      => 'datetime',
        'deleted_epoch' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            $model->save();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    
    public function creator(): BelongsTo { 
        return $this->belongsTo(Admin::class, 'created_by_id'); 
    }

    public function updater(): BelongsTo { 
        return $this->belongsTo(Admin::class, 'updated_by_id'); 
    }

    protected function listPrice(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_numeric($value) ? (float) $value : 0.00,
            get: fn ($value) => (float) $value
        );
    }

    protected function finalPrice(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_numeric($value) ? (float) $value : 0.00,
            get: fn ($value) => (float) $value
        );
    }

    protected function minQuantity(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_numeric($value) ? (int) $value : 1,
            get: fn ($value) => (int) $value
        );
    }
}