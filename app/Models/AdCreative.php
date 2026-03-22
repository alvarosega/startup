<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\{MorphTo, BelongsTo};

class AdCreative extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'branch_id',    // Anclaje único
        'campaign_id', 
        'placement_id', 
        'category_id',  // Anclaje a categoría
        'target_type', 
        'target_id',
        'name', 
        'image_mobile_path', 
        'image_desktop_path',
        'action_type', 
        'sort_order', 
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function target(): MorphTo { return $this->morphTo(); }
    public function campaign(): BelongsTo { return $this->belongsTo(AdCampaign::class); }
    public function placement(): BelongsTo { return $this->belongsTo(AdPlacement::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
}