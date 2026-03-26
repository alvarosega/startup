<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Sku extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id', 'name', 'code', 'base_price', 
        'conversion_factor', 'weight', 'image_path',
        'sort_order', 'is_active'
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'sort_order'        => 'integer',
        'base_price'        => 'decimal:2',
        'weight'            => 'decimal:3',
        'conversion_factor' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::creating(function (Sku $sku) {
            // LEY DE IDENTIDAD: EAN-13 Automático si el code es null
            if (empty($sku->code)) {
                $sku->code = self::generateInternalEan();
            }
        });

        static::updating(function (Sku $sku) {
            // PROTOCOLO DE TRAZABILIDAD: Inmutabilidad del código asignado
            if ($sku->isDirty('code') && !empty($sku->getOriginal('code'))) {
                throw new \Exception("VIOLACIÓN_DE_PROTOCOLO: El código SKU es inmutable.");
            }
        });
    }

    private static function generateInternalEan(): string
    {
        $base = '21' . substr(number_format(microtime(true) * 1000, 0, '', ''), -10);
        $sum = 0;
        foreach (str_split($base) as $i => $digit) {
            $sum += $digit * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $base . $checkDigit;
    }

    // --- RELACIONES (PROTOCOLOS DE ENLACE) ---

    public function product(): BelongsTo 
    { 
        return $this->belongsTo(Product::class); 
    }

    public function prices(): HasMany 
    { 
        return $this->hasMany(Price::class); 
    }

    public function inventoryLots(): HasMany 
    { 
        return $this->hasMany(InventoryLot::class); 
    }

    // --- SCOPES (LA RECTIFICACIÓN) ---

    /**
     * Scope para filtrar SKUs activos en el sistema.
     * Requerido por: HeroBannerSeeder, InventorySeeder.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // --- ATRIBUTOS (CLEAN CASTING) ---

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path 
            ? asset('storage/' . $this->image_path) 
            : asset('assets/img/placeholders/sku-default.png'));
    }

    protected function basePrice(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_numeric($value) ? $value : 0.00,
            get: fn ($value) => (float) $value
        );
    }

    protected function weight(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_numeric($value) ? $value : 0.000,
            get: fn ($value) => (float) $value
        );
    }

    protected function conversionFactor(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_numeric($value) ? $value : 1.000,
            get: fn ($value) => (float) $value
        );
    }

    // --- MÉTODOS ESTÁTICOS ---

    public static function getAvailableForBundles()
    {
        return self::select(['id', 'product_id', 'name', 'base_price'])
            ->with('product:id,name')
            ->active()
            ->orderBy('name')
            ->get();
    }
    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(Bundle::class, 'bundle_items')
                    ->withPivot('quantity')
                    ->withTimestamps()
                    ->using(BundleItem::class);
    }
}