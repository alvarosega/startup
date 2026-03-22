<?php 
namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MarketZone extends Model
{
    use SoftDeletes, HasUv7;

    protected $fillable = ['name', 'slug', 'hex_color', 'svg_id', 'description', 'is_active'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function brands(): BelongsToMany { 
        return $this->belongsToMany(Brand::class, 'brand_market_zone'); 
    }
}