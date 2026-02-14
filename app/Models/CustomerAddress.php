<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CustomerAddress extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'customer_id', 'branch_id', 'alias', 'address', 
        'latitude', 'longitude', 'reference', 'is_default'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_default' => 'boolean',
    ];

    protected $hidden = ['id', 'customer_id', 'branch_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = hex2bin(str_replace('-', '', (string) Str::uuid()));
            }
        });
    }

    // --- INTERCEPTOR DE RUTAS (VITAL) ---
    // Esto permite que la ruta /addresses/d55bb... encuentre el registro en BD
    public function resolveRouteBinding($value, $field = null)
    {
        // Si el ID en la URL es Hex (32 chars), lo pasamos a Binario para buscar
        if (is_string($value) && ctype_xdigit($value) && strlen($value) === 32) {
            $value = hex2bin($value);
        }
        return parent::resolveRouteBinding($value, $field);
    }

    // --- INTERCEPTOR DE CONSULTAS ---
    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                $binaries = ['id', 'customer_id', 'branch_id'];
                $colName = str_contains($column, '.') ? explode('.', $column)[1] : $column;

                if (in_array($colName, $binaries)) {
                    if (func_num_args() === 2) { $value = $operator; $operator = '='; }
                    if (is_string($value) && ctype_xdigit($value) && strlen($value) === 32) {
                        $value = hex2bin($value);
                    }
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }

    public function toArray()
    {
        $array = parent::toArray();
        if ($this->getRawOriginal('id')) $array['id'] = bin2hex($this->getRawOriginal('id'));
        if ($this->getRawOriginal('customer_id')) $array['customer_id'] = bin2hex($this->getRawOriginal('customer_id'));
        if ($this->getRawOriginal('branch_id')) $array['branch_id'] = bin2hex($this->getRawOriginal('branch_id'));
        return $array;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}