<?php

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; 

class Brand extends Model
{
    use SoftDeletes, HasUv7;

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'sort_order'  => 'integer',
    ];

    protected $fillable = [
        'parent_id', 'provider_id', 'category_id', 'name', 'slug', 'bg_color', // <--- AGREGADO
        'image_path', 'website', 'is_active', 'is_featured', 'sort_order', 'description'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    // --- RELACIONES (PROTOCOLOS DE ENLACE) ---

    /**
     * RELACIÓN CRÍTICA: Una marca tiene múltiples productos.
     * Requerida por el Scope de Stock.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function provider(): BelongsTo { 
        return $this->belongsTo(Provider::class); 
    }

    public function marketZones(): BelongsToMany { 
        return $this->belongsToMany(MarketZone::class, 'brand_market_zone'); 
    }
    
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    
    public function subBrands(): HasMany { return $this->hasMany(self::class, 'parent_id'); }

    // --- SCOPES (PROTOCOLOS DE FILTRADO) ---

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function inventoryBalances(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            InventoryBalance::class,
            Product::class,
            'brand_id', // Local key en Products
            'sku_id',   // No se usa directamente aquí pero es parte de la cadena
            'id',       // Local key en Brands
            'id'        // Se resolverá vía la relación de Product
        );
    }
    public function scopeWhereHasStockInBranch($query, $branchId)
    {
        return $query->whereHas('products.inventoryBalances', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->where('total_physical', '>', DB::raw('total_reserved + total_safety'));
        });
    }

    // --- ATRIBUTOS ---

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path 
            ? Storage::disk('public')->url($this->image_path) 
            : null);
    }
}