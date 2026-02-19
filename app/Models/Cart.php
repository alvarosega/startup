<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Cart extends Model
{
    use HasUuids; // <--- 2. IMPORTANTE (Faltaba esto)

    protected $table = 'carts';
    protected $fillable = ['session_id', 'user_id', 'branch_id'];

    public function items() { return $this->hasMany(CartItem::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function user() { return $this->belongsTo(User::class); }

    // PROTECCIÓN MANUAL PARA LLAVES FORÁNEAS BINARIAS
    public function getUserIdAttribute($value)
    {
        if (is_string($value) && strlen($value) === 16) return bin2hex($value);
        return $value;
    }

    public function getBranchIdAttribute($value)
    {
        if (is_string($value) && strlen($value) === 16) return bin2hex($value);
        return $value;
    }
}