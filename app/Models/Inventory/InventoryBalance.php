<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    use HasFactory;

    // RECTIFICACIÓN (OPCIÓN B): Desactivar el incremento y la asunción de clave única 'id'.
    // Este modelo opera exclusivamente para consultas de lectura en la capa web/API.
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'branch_id',
        'sku_id',
        'total_physical',
        'total_reserved',
        'total_safety',
        'total_quarantine'
    ];

    protected $casts = [
        'total_physical'   => 'float',
        'total_reserved'   => 'float',
        'total_safety'     => 'float',
        'total_quarantine' => 'float'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class);
    }
}