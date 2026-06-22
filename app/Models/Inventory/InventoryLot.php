<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryLot extends Model
{
    use HasFactory, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'purchase_id',
        'transfer_id',
        'branch_id',
        'sku_id',
        'lot_code',
        'quantity',
        'initial_quantity',
        'safety_quantity',
        'initial_safety_quantity',
        'reserved_quantity',
        'is_quarantine',
        'expiration_date'
    ];

    protected $casts = [
        'quantity'                => 'float',
        'initial_quantity'        => 'float',
        'safety_quantity'         => 'float',
        'initial_safety_quantity' => 'float',
        'reserved_quantity'       => 'float',
        'is_quarantine'           => 'boolean',
        'expiration_date'         => 'date'
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}