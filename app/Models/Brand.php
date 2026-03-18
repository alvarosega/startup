<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'provider_id', 'category_id', 'market_zone_id',
        'name', 'slug', 'image_path', 'website', 'is_active', 
        'is_featured', 'sort_order', 'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $hidden = ['deleted_at'];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }


    // =========================================================================
    // ACCESORES (SANITIZACIÓN)
    // =========================================================================

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function marketZone(): \Illuminate\Database\Eloquent\Relations\BelongsTo 
    { 
        return $this->belongsTo(MarketZone::class); 
    }
    
    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany 
    { 
        return $this->hasMany(Product::class); 
    }
    
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo 
    { 
        return $this->belongsTo(Category::class); 
    }

    // app/Models/Brand.php

    public function scopeWhereHasStockInBranch($query, string $branchId)
    {
        return $query->where('is_active', true)
            ->whereExists(function ($query) use ($branchId) {
                $query->select(\DB::raw(1))
                    ->from('products')
                    ->join('skus', 'products.id', '=', 'skus.product_id')
                    ->join('inventory_lots', 'skus.id', '=', 'inventory_lots.sku_id')
                    ->whereColumn('products.brand_id', 'brands.id')
                    ->where('products.is_active', true)
                    ->where('skus.is_active', true)
                    ->where('inventory_lots.branch_id', $branchId)
                    ->where('inventory_lots.is_safety_stock', false)
                    ->whereRaw('(inventory_lots.quantity - inventory_lots.reserved_quantity) > 0');
            });
    }
}