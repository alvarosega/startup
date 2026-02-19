<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'branch_id', 'provider_id', 'user_id', 
        'document_number', 'purchase_date', 
        'payment_type', 'payment_due_date', 
        'total_amount', 'notes', 'status'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'payment_due_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function inventoryLots() { return $this->hasMany(InventoryLot::class); }
    // Dentro de la clase Purchase y InventoryMovement

    // Mutador: Convierte String Hex -> Binario al guardar
    public function setAdminIdAttribute($value)
    {
        if (is_string($value) && ctype_xdigit($value) && strlen($value) === 32) {
            $this->attributes['admin_id'] = hex2bin($value);
        } else {
            $this->attributes['admin_id'] = $value;
        }
    }

    // Accesor (Opcional): Para leerlo como String después
    public function getAdminIdAttribute($value)
    {
        if (is_string($value) && !ctype_print($value)) {
            return bin2hex($value);
        }
        return $value;
    }
}