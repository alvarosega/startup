<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransformation extends Model
{
    use HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'branch_id', 'admin_id', 'source_inventory_lot_id',
        'quantity_removed', 'destination_inventory_lot_id', 'quantity_added', 'notes'
    ];

    protected $casts = [
        'quantity_removed' => 'float',
        'quantity_added'   => 'float',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\Admin::class);
    }

    public function sourceLot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class, 'source_inventory_lot_id');
    }

    public function destinationLot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class, 'destination_inventory_lot_id');
    }
}