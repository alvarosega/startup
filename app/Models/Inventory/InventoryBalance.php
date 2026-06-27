<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    /**
     * Llave primaria compuesta resguardada por definición de transporte plano.
     */
    protected $primaryKey = ['branch_id', 'sku_id'];
    public $incrementing = false;

    protected $fillable = [
        'branch_id', 'sku_id', 'total_physical', 'total_reserved', 'total_safety', 'total_quarantine'
    ];

    protected $casts = [
        'total_physical'   => 'float',
        'total_reserved'   => 'float',
        'total_safety'     => 'float',
        'total_quarantine' => 'float',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class, 'branch_id');
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class, 'sku_id');
    }
}