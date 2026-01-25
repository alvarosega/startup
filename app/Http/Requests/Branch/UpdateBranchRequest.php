<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $branchId = $this->route('branch')->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('branches')->ignore($branchId)],
            'city' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'coverage_polygon' => ['required', 'array', 'min:3'],
            'coverage_polygon.*' => ['array', 'size:2'],
            'is_active' => ['boolean'],
        ];
    }
}