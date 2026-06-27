<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['branch_id', 'sku_id'];
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id', 'sku_id', 'total_physical', 'total_reserved',
        'total_safety', 'total_quarantine'
    ];

    protected $casts = [
        'total_physical' => 'float',
        'total_reserved' => 'float',
        'total_safety' => 'float',
        'total_quarantine' => 'float'
    ];

    /**
     * Sobrescritura obligatoria para dar soporte a llaves primarias compuestas en Eloquent
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where('branch_id', $this->getAttribute('branch_id'))
              ->where('sku_id', $this->getAttribute('sku_id'));
        return $query;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class);
    }
}