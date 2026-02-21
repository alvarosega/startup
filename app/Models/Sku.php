<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Sku extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id', 'name', 'code', 'base_price', 
        'conversion_factor', 'weight', 'image_path', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
        'conversion_factor' => 'decimal:3',
        'weight' => 'decimal:3'
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function prices(): HasMany { return $this->hasMany(Price::class); }
}