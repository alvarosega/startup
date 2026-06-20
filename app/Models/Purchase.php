<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id', 
        'provider_id', 
        'admin_id', 
        'document_number', 
        'purchase_date', 
        'payment_type', 
        'status',
        'deleted_epoch'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'deleted_epoch' => 'integer'
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            $model->save();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function admin(): BelongsTo { return $this->belongsTo(Admin::class, 'admin_id'); }
    
    public function purchaseItems(): HasMany 
    { 
        return $this->hasMany(PurchaseItem::class); 
    }
    
    public function inventoryLots(): HasMany 
    { 
        return $this->hasMany(InventoryLot::class); 
    }
}