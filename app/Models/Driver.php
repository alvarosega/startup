<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Driver extends Authenticatable
{
    use HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = ['id', 'phone', 'email', 'password', 'status'];
    protected $hidden = ['password', 'remember_token'];

    /**
     * Accesor y Mutador de ID: El "corazón" de la solución.
     * Convierte Binario <-> Hexadecimal automáticamente.
     */
    protected function id(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_null($value)) return null;
                // Si tiene 16 bytes y no es texto imprimible, es binario
                return (strlen($value) === 16 && !ctype_print($value)) 
                    ? bin2hex($value) 
                    : $value;
            },
            set: function ($value) {
                if (is_null($value)) return null;
                // Si recibimos un HEX de 32 caracteres, lo guardamos como binario
                return (is_string($value) && strlen($value) === 32 && ctype_xdigit($value)) 
                    ? hex2bin($value) 
                    : $value;
            }
        );
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = hex2bin(str_replace('-', '', (string) Str::uuid()));
            }
        });
    }

    // Eliminamos toArray() y getAuthIdentifier() manuales para dejar que Attribute haga su trabajo

    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if ($column === 'id' || $column === 'drivers.id') {
                    if (func_num_args() === 2) { $value = $operator; $operator = '='; }
                    if (is_string($value) && ctype_xdigit($value) && strlen($value) === 32) {
                        $value = hex2bin($value);
                    }
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }

    public function details()
    {
        return $this->hasOne(DriverDetail::class, 'driver_id', 'id');
    }
}