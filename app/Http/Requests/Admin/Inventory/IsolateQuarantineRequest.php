<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IsolateQuarantineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'inventory_lot_id' => [
                'required', 
                'string', 
                'uuid', 
                Rule::exists('inventory_lots', 'id')->where('deleted_epoch', 0)
            ],
            'is_quarantine' => [
                'required', 
                'boolean'
            ],
        ];
    }
}