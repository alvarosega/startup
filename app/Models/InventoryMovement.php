<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'sku_id',
        'inventory_lot_id',
        'admin_id', 
        'type',
        'quantity',
        'unit_cost',
        'reference'
    ];

    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }
    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    // RELACIÓN FALTANTE Y CRÍTICA
    public function lot(): BelongsTo { return $this->belongsTo(InventoryLot::class, 'inventory_lot_id'); }
}