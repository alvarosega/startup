<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Brand extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    // RECTIFICACIÓN: Se incorpora 'id' al fillable para permitir la asignación explícita de UUIDs en la suite de pruebas
    protected $fillable = [
        'id', 'parent_id', 'provider_id', 'category_id', 'name', 'slug', 'bg_color',
        'image_path', 'website', 'is_active', 'is_featured', 'sort_order', 'description', 'deleted_epoch'
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'is_featured'   => 'boolean',
        'sort_order'    => 'integer',
        'deleted_epoch' => 'integer',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            // RECTIFICACIÓN: Mutación silenciosa para evitar ciclos redundantes e infinitos de hooks de guardado
            $model->saveQuietly();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    public function products(): HasMany 
    { 
        return $this->hasMany(Product::class); 
    }

    public function provider(): BelongsTo 
    { 
        return $this->belongsTo(\App\Models\Operations\Provider::class); 
    }

    public function category(): BelongsTo 
    { 
        return $this->belongsTo(Category::class); 
    }

    public function subBrands(): HasMany 
    { 
        return $this->hasMany(self::class, 'parent_id'); 
    }

    public function marketZones(): BelongsToMany 
    { 
        return $this->belongsToMany(\App\Models\MarketZone::class, 'brand_market_zone'); 
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWhereHasStockInBranch($query, string $branchId)
    {
        return $query->whereHas('products', function ($q) use ($branchId) {
            $q->where('is_active', true)
              ->whereHas('skus', function ($q) use ($branchId) {
                  $q->where('is_active', true)
                    ->whereHas('inventoryLots', function ($sub) use ($branchId) {
                        $sub->where('branch_id', $branchId)
                            ->whereRaw('(quantity - reserved_quantity) > 0');
                    });
              });
        });
    }
}