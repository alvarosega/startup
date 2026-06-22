<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\RetailMedia\Placement;

use Illuminate\Foundation\Http\FormRequest;

class StorePlacementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:64'],
            'code'      => ['required', 'string', 'max:32', 'unique:ad_placements,code'],
            'max_items' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}