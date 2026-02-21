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
        'final_price', 'min_quantity', 'priority', 'valid_from', 'valid_to'
    ];

    protected $casts = [
        'list_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
}