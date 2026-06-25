<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Operations\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    /**
     * Valida de forma estricta que el usuario autenticado posea el rol administrativo requerido.
     */
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch') instanceof \App\Models\Operations\Branch 
            ? $this->route('branch')->id 
            : $this->route('branch');

        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('branches', 'name')->ignore($branchId)->where('deleted_epoch', 0)
            ],
            'city' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'coverage_polygon' => ['required', 'array', 'min:3'],
            'coverage_polygon.*' => ['required', 'array', 'min:2'],
            'coverage_polygon.*.*' => ['required', 'numeric'],
            'is_active' => ['required', 'boolean'],
            'is_default' => ['required', 'boolean'],
            'delivery_base_fee' => ['required', 'numeric', 'min:0'],
            'delivery_price_per_km' => ['required', 'numeric', 'min:0'],
            'surge_multiplier' => ['required', 'numeric', 'min:1'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'small_order_fee' => ['required', 'numeric', 'min:0'],
            'base_service_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}