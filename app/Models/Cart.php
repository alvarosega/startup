<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasUuids;

    protected $table = 'carts';

    // Se corrigió user_id por customer_id para alinearlo con la migración
    protected $fillable = ['session_id', 'customer_id', 'branch_id'];

    public function items(): HasMany { return $this->hasMany(CartItem::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}