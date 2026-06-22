<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasFactory, SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sku_id',
        'branch_id',
        'type',
        'list_price',
        'final_price',
        'min_quantity',
        'priority',
        'deleted_epoch',
        'valid_from',
        'valid_to',
        'created_by_id',
        'updated_by_id'
    ];

    protected $casts = [
        'list_price'    => 'float',
        'final_price'   => 'float',
        'min_quantity'  => 'integer',
        'priority'      => 'integer',
        'deleted_epoch' => 'integer',
        'valid_from'    => 'datetime',
        'valid_to'      => 'datetime'
    ];

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
        return $this->belongsTo(\App\Models\Admin::class, 'created_by_id');
    }
}