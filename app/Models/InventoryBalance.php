<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['branch_id', 'sku_id'];

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

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('branch_id', $this->getAttribute('branch_id'))
                     ->where('sku_id', $this->getAttribute('sku_id'));
    }

    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
}