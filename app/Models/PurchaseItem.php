<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'purchase_id',
        'sku_id',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'float' 
    ];

    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
}