<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpsertCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_id'   => ['required', 'string'],
            'target_type' => ['required', 'in:sku,bundle'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }
}