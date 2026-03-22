<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdPlacement extends Model {
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name', 'code', 'max_items', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'max_items' => 'integer'];

    public function creatives(): HasMany { return $this->hasMany(AdCreative::class, 'placement_id'); }
}