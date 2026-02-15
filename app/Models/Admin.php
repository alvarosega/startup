<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $guard_name = 'super_admin';

    protected $fillable = [
        'id', 'first_name', 'last_name', 'phone', 'email', 'password', 'branch_id', 'is_active'
    ];

    protected $hidden = [
        'password', 
        'remember_token',
        // BLINDAJE: Ocultar campos binarios para evitar errores de serialización JSON
        'id', 
        'branch_id' 
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];


    public function toArray()
    {
        // parent::toArray() respetará el $hidden, eliminando los binarios
        $array = parent::toArray();
     
        // Inyectamos manualmente las versiones Hexadecimales para Vue
        $rawId = $this->getRawOriginal('id');
        $array['id'] = $rawId ? bin2hex($rawId) : null;
        
        $rawBranch = $this->getRawOriginal('branch_id');
        if ($rawBranch) {
            $array['id_branch'] = bin2hex($rawBranch); // Cambiado a id_branch por claridad
        }
        
        return $array;
    }

    public function getKey()
    {
        // Obtenemos el valor original de la base de datos (siempre binario)
        return $this->getRawOriginal('id') ?? $this->getAttribute('id');
    }

   
    public function getAuthIdentifier()
    {
       
        return bin2hex($this->getRawOriginal('id'));
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->getAttribute('id'))) {
                
                $model->setAttribute('id', hex2bin(str_replace('-', '', (string) \Illuminate\Support\Str::uuid())));
            }
        });
    }

    
    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                $binaryColumns = ['id', 'admins.id', 'branch_id'];
                if (in_array($column, $binaryColumns)) {
                    if (func_num_args() === 2) { $value = $operator; $operator = '='; }
                    if (is_string($value) && ctype_xdigit($value) && strlen($value) === 32) {
                        $value = hex2bin($value);
                    }
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }
    
    public function resolveRouteBinding($value, $field = null)
    {
        $binValue = (is_string($value) && ctype_xdigit($value) && strlen($value) === 32) ? hex2bin($value) : $value;
        return $this->where('id', $binValue)->first() ?? abort(404);
    }

    public function getFullNameAttribute() { return "{$this->first_name} {$this->last_name}"; }
    public function branch() { return $this->belongsTo(Branch::class); }

}