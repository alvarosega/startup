<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdCreative extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'ad_creatives';

    protected $fillable = [
        'campaign_id',
        'placement_id',
        'branch_id',
        'sku_id',
        'category_id',
        'bundle_id',
        'brand_id',
        'version',
        'target_id',
        'target_type',
        'name',
        'image_mobile_path',
        'image_desktop_path',
        'action_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'version'    => 'integer',
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    // --- RELACIONES DE INFRAESTRUCTURA PUBLICITARIA ---

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'campaign_id');
    }

    public function placement(): BelongsTo
    {
        return $this->belongsTo(AdPlacement::class, 'placement_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // --- RELACIONES DE ANCLAJE CONTEXTUAL (NULLABLE) ---

    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class, 'sku_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    // --- RELACIÓN POLIMÓRFICA DE DESTINO (TARGET) ---

    /**
     * Resuelve dinámicamente la entidad interna de destino (Sku, Category, Bundle).
     */
    public function target(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }
}