<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasPrefixedId
{
    /**
     * Define el prefijo en el modelo: protected $prefix = 'usr';
     */

    // 1. Al serializar a JSON, agregamos el prefijo (OUTPUT)
    public function toArray()
    {
        $array = parent::toArray();
        // Sobreescribimos el ID con el prefijo
        $array['id'] = $this->getPrefixedId();
        return $array;
    }

    public function getPrefixedId(): string
    {
        $prefix = property_exists($this, 'prefix') ? $this->prefix : 'gen';
        return $prefix . '_' . $this->getKey();
    }

    // 2. Al recibir en URL, limpiamos el prefijo (INPUT)
    public function resolveRouteBinding($value, $field = null)
    {
        // Si el valor viene con prefijo (ej: usr_018c...), lo limpiamos
        $prefix = property_exists($this, 'prefix') ? $this->prefix : 'gen';
        
        if (str_starts_with($value, $prefix . '_')) {
            $value = substr($value, strlen($prefix) + 1);
        }

        return parent::resolveRouteBinding($value, $field);
    }
}