<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CustomerProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'customer_id', 
        'first_name', 
        'last_name', 
        'birth_date', 
        'gender', 
        'avatar_type', 
        'avatar_source',
        // AÑADIR ESTOS:
        'latitude',
        'longitude',
        'address'
    ];

    protected $hidden = ['customer_id'];

    // ❌ ELIMINADOS: getCustomerIdAttribute y setCustomerIdAttribute
    // Ahora customer_id será binario puro en memoria.

    // --- INTERCEPTOR DE CONSULTAS (Sigue siendo vital para búsquedas externas) ---
    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if ($column === 'customer_id' || $column === 'customer_profiles.customer_id') {
                    if (func_num_args() === 2) { $value = $operator; $operator = '='; }
                    // Si buscamos con Hex, convertimos a Binario
                    if (is_string($value) && ctype_xdigit($value) && strlen($value) === 32) {
                        $value = hex2bin($value);
                    }
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    // --- SERIALIZACIÓN (Único lugar donde se vuelve Hex) ---
    public function toArray()
    {
        $array = parent::toArray();
        if ($this->getRawOriginal('customer_id')) {
            $array['customer_id'] = bin2hex($this->getRawOriginal('customer_id'));
        }
        return $array;
    }
}