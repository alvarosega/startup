<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferSafetyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('super_admin');
        return $user && ($user->hasRole('super_admin') || $user->hasPermissionTo('manage_inventory', 'super_admin'));
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
            'quantity' => [
                'required', 
                'numeric', 
                'min:0.001'
            ],
        ];
    }
}