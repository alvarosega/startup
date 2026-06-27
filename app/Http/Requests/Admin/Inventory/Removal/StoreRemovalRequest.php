<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory\Removal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRemovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'string', 'uuid', Rule::exists('branches', 'id')->where('is_active', true)],
            'reason' => ['required', 'string', Rule::in(['expiration', 'damage', 'theft', 'internal_use', 'admin_error'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_lot_id' => ['required', 'string', 'uuid', Rule::exists('inventory_lots', 'id')->where('deleted_epoch', 0)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0.00'],
        ];
    }
}