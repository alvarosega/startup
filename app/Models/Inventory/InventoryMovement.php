<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';
    
    // El Kardex opera bajo inserciones puras cronológicas sin mutación posterior
    const UPDATED_AT = null;

    protected $fillable = [
        'branch_id',
        'sku_id',
        'inventory_lot_id',
        'admin_id',
        'type',
        'quantity',
        'reference',
        'reason'
    ];

    protected $casts = [
        'quantity'   => 'float',
        'created_at' => 'datetime'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class, 'inventory_lot_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class);
    }
}