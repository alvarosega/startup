<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; 

    protected $fillable = [
        'branch_id', 'sku_id', 'inventory_lot_id', 'admin_id', 
        'type', 'quantity', 'unit_cost', 'reference', 'created_at' // Asegúrate de incluirlo si haces inserción manual
    ];

    // LEY: Forzar el casteo a datetime para que el Resource reciba un objeto Carbon
    protected $casts = [
        'created_at' => 'datetime',
        'unit_cost'  => 'decimal:2',
        'quantity'   => 'integer'
    ];

    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }
    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function lot(): BelongsTo { return $this->belongsTo(InventoryLot::class, 'inventory_lot_id'); }
}