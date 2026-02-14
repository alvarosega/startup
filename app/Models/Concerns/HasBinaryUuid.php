<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasBinaryUuid
{
    public function initializeHasBinaryUuid()
    {
        $this->incrementing = false;
        $this->keyType = 'string';
    }

    public static function bootHasBinaryUuid()
    {
        static::creating(function (Model $model) {
            $keyName = $model->getKeyName();
            if (empty($model->{$keyName})) {
                $model->attributes[$keyName] = hex2bin(str_replace('-', '', (string) Str::uuid()));
            }
        });
    }

    public function getKey()
    {
        $key = parent::getKey();
        return (is_string($key) && strlen($key) === 16) ? bin2hex($key) : $key;
    }

    /**
     * Esta es la solución definitiva al error 500:
     * Convierte TODO lo binario a Hexadecimal antes de que Laravel intente hacer el JSON.
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        foreach ($attributes as $key => $value) {
            if (is_string($value) && strlen($value) === 16) {
                $attributes[$key] = bin2hex($value);
            }
        }
        return $attributes;
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                // CORRECCIÓN: Si $column es un array (usado en updateOrCreate), 
                // dejamos que Eloquent lo maneje recursivamente.
                if (is_array($column)) {
                    return parent::where($column, $operator, $value, $boolean);
                }

                // Lógica original para Strings
                if (func_num_args() === 2) { $value = $operator; $operator = '='; }

                // Conversión automática Hex -> Binario para consultas
                if (is_string($column) && str_ends_with($column, 'id') && is_string($value) && ctype_xdigit($value) && strlen($value) === 32) {
                    $value = hex2bin($value);
                }

                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        // Si es binario de 16 bytes y parece un ID, devuélvelo como HEX
        if (is_string($value) && strlen($value) === 16) {
            if ($key === $this->getKeyName() || str_ends_with($key, '_id')) {
                return bin2hex($value);
            }
        }
        return $value;
    }

    // 2. Intercepta la escritura ($address->branch_id = 'hex') y lo convierte a Binario
    // Esto evita el error "Data too long" al guardar desde el formulario
    public function setAttribute($key, $value)
    {
        if (is_string($value) && strlen($value) === 32 && ctype_xdigit($value)) {
            if ($key === $this->getKeyName() || str_ends_with($key, '_id')) {
                $value = hex2bin($value);
            }
        }
        return parent::setAttribute($key, $value);
    }
    private function resolveShopContext()
    {
        try {
            $contextService = app(ShopContextService::class);
            $rawId = $contextService->getActiveBranchId();
            
            // CORRECCIÓN CRÍTICA: Convertimos binario a Hex antes de enviar
            $activeBranchId = (is_string($rawId) && strlen($rawId) === 16 && !ctype_print($rawId))
                ? bin2hex($rawId)
                : $rawId;
            
            $activeBranchName = 'Desconocida';
            if ($activeBranchId) {
                $branch = Branch::find($activeBranchId); 
                if ($branch) $activeBranchName = $branch->name;
            }

            return [
                'branch_id' => $activeBranchId, // Ahora es texto seguro
                'branch_name' => $activeBranchName,
                'is_fallback' => ($activeBranchId == 1 || $activeBranchId === null) && !session('shop_address_id'),
            ];
        } catch (\Throwable $e) {
            return ['branch_name' => 'Tienda', 'is_fallback' => false];
        }
    }
}