<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Cache;

class Branch extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'phone', 'city', 'address', 
        'coverage_polygon', 'opening_hours', 'is_active', 'is_default', 'latitude', 'longitude'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_hours' => 'array',
        'coverage_polygon' => 'array',
        'is_default' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }   
    
    protected static function booted()
    {
        static::saving(function ($branch) {
            // Si esta sucursal se marca como default, desmarcar las demás
            if ($branch->is_default) {
                static::where('id', '!=', $branch->id)->update(['is_default' => false]);
                Cache::forget('shop_default_branch_id');
            }
        });
    }
    public static function getMinimalList()
    {
        return self::orderBy('name')->get(['id', 'name']);
    }
}