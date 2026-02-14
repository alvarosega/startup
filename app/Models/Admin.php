<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $guard_name = 'admin';

    protected $fillable = [
        'id', 'first_name', 'last_name', 'phone', 'email', 'password', 'role_level', 'branch_id', 'is_active'
    ];

    // IMPORTANTE: Asegúrate de que 'id' y 'branch_id' estén ocultos para evitar la serialización binaria accidental
    protected $hidden = ['password', 'remember_token', 'id', 'branch_id'];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // 1. SERIALIZACIÓN: Único lugar donde el Binario se vuelve Hexadecimal
    public function toArray()
    {
        $array = parent::toArray();
        $rawId = $this->getRawOriginal('id');
        if ($rawId) {
            $array['id'] = bin2hex($rawId);
        }
        
        $rawBranch = $this->getRawOriginal('branch_id');
        if ($rawBranch) {
            $array['branch_id'] = bin2hex($rawBranch);
        }
        return $array;
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

    // ❌ ELIMINADOS: getIdAttribute y setIdAttribute (Bórralos de tu archivo)

    // 2. IDENTIDAD DE LOGIN: Debe devolver el Hex
    public function getAuthIdentifier()
    {
        // Auth de Laravel necesita un String, convertimos el binario a hex aquí
        return bin2hex($this->getRawOriginal('id'));
    }

    // 3. INTERCEPTOR DE CONSULTAS (Mantenlo, está bien hecho)
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