<?php

declare(strict_types=1);

namespace App\Models\Bundle;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleItem extends Model
{
    use HasFactory, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'bundle_id',
        'sku_id',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'float'
    ];

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class, 'sku_id');
    }
}