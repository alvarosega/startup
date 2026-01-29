<?php

namespace App\Http\Requests\Market;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarketZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $zoneId = $this->route('market_zone')?->id;
    
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('market_zones', 'name')->ignore($zoneId)],
            'hex_color' => ['required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'svg_id' => ['required', 'string', 'max:50', Rule::unique('market_zones', 'svg_id')->ignore($zoneId)],
            'description' => ['nullable', 'string', 'max:500'],
            
            // NUEVO: Array de IDs de categorías
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'] 
        ];
    }

    public function messages(): array
    {
        return [
            'hex_color.regex' => 'El color debe ser un código hexadecimal válido (ej: #FF0000).',
            'svg_id.unique' => 'Este ID de SVG ya está asignado a otra zona.',
        ];
    }
}