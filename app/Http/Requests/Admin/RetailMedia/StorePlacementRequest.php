<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\RetailMedia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlacementRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $placementId = $this->route('ad_placement');

        return [
            'name'      => ['required', 'string', 'max:64'],
            'code'      => ['required', 'string', 'max:32', 'alpha_dash', Rule::unique('ad_placements', 'code')->ignore($placementId)],
            'max_items' => ['required', 'integer', 'min:1', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}