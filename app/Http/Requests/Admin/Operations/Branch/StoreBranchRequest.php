<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Operations\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                        => ['required', 'string', 'max:255', Rule::unique('branches', 'name')->where('deleted_epoch', 0)],
            'city'                        => ['required', 'string', 'max:100'],
            'phone'                       => ['nullable', 'string', 'max:20'],
            'address'                     => ['nullable', 'string', 'max:500'],
            'latitude'                    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'                   => ['nullable', 'numeric', 'between:-180,180'],
            'coverage_polygon'            => ['nullable', 'array'],
            'opening_hours'               => ['nullable', 'array'],
            'is_active'                   => ['required', 'boolean'],
            'is_default'                  => ['required', 'boolean'],
            'delivery_base_fee'           => ['required', 'numeric', 'min:0'],
            'delivery_price_per_km'       => ['required', 'numeric', 'min:0'],
            'surge_multiplier'            => ['required', 'numeric', 'min:1'],
            'min_order_amount'            => ['required', 'numeric', 'min:0'],
            'small_order_fee'             => ['required', 'numeric', 'min:0'],
            'base_service_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}