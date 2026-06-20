<?php

namespace App\Http\Requests\Admin\MarketZone;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMarketZoneRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $zoneId = $this->route('market_zone')?->id;

        return [
            'name'        => ['required', 'string', 'max:255', Rule::unique('market_zones')->ignore($zoneId)->withoutTrashed()],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('market_zones')->ignore($zoneId)->withoutTrashed()],
            'hex_color'   => ['nullable', 'string', 'max:7', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'svg_id'      => ['nullable', 'string', 'max:255', Rule::unique('market_zones')->ignore($zoneId)->withoutTrashed()],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ];
    }
}