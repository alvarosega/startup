<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Price extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sku_id', 'branch_id', 'type', 'list_price', 
        'final_price', 'min_quantity', 'priority', 'valid_from', 'valid_to',
        'created_by_id', 'updated_by_id' // <--- NUEVOS CAMPOS
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    // Relaciones para auditoría
    public function creator(): BelongsTo { 
        return $this->belongsTo(Admin::class, 'created_by_id'); 
    }

    public function updater(): BelongsTo { 
        return $this->belongsTo(Admin::class, 'updated_by_id'); 
    }
    public function getListPriceAttribute($value): float
    {
        $clean = str_replace(',', '.', (string) $value);
        return is_numeric($clean) ? (float) $clean : 0.00;
    }

    public function getFinalPriceAttribute($value): float
    {
        $clean = str_replace(',', '.', (string) $value);
        return is_numeric($clean) ? (float) $clean : 0.00;
    }

    public function getMinQuantityAttribute($value): int
    {
        $clean = str_replace(',', '.', (string) $value);
        return is_numeric($clean) ? (int) $clean : 1;
    }
}