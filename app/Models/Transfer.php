<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code', 
        'origin_branch_id', 
        'destination_branch_id', 
        'created_by', 
        'received_by', 
        'status', 
        'notes', 
        'shipped_at', 
        'received_at'
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function items(): HasMany 
    { 
        return $this->hasMany(TransferItem::class); 
    }

    public function origin(): BelongsTo 
    { 
        return $this->belongsTo(Branch::class, 'origin_branch_id'); 
    }

    public function destination(): BelongsTo 
    { 
        return $this->belongsTo(Branch::class, 'destination_branch_id'); 
    }

    public function sender(): BelongsTo 
    { 
        return $this->belongsTo(Admin::class, 'created_by'); 
    }

    public function receiver(): BelongsTo 
    { 
        return $this->belongsTo(Admin::class, 'received_by'); 
    }
}