<?php

declare(strict_types=1);

namespace App\Models\RetailMedia;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdCreative extends Model
{
    use HasFactory, SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'campaign_id',
        'placement_id',
        'branch_id',
        'sku_id',
        'category_id',
        'brand_id',
        'bundle_id',
        'name',
        'image_mobile_path',
        'image_desktop_path',
        'action_type',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean'
    ];

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
        return $this->belongsTo(\App\Models\Operations\Branch::class, 'branch_id');
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class, 'sku_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Category::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Brand::class, 'brand_id');
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Bundle\Bundle::class, 'bundle_id');
    }
}