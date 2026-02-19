<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InventoryMovement extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'branch_id', 'sku_id', 'inventory_lot_id', 'user_id',
        'type', 'quantity', 'unit_cost', 'reference'
    ];
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
