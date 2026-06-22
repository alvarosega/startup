<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\RetailMedia\Campaign;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'        => ['required', 'string', Rule::in(['PAID', 'INTERNAL'])],
            'provider_id' => [
                'nullable',
                Rule::requiredIf($this->type === 'PAID'), // Obligatorio solo si la campaña es pagada
                'uuid',
                Rule::exists('providers', 'id')->where('deleted_epoch', 0)
            ],
            'name'        => ['required', 'string', 'max:128'],
            'starts_at'   => ['nullable', 'date'],
            'ends_at'     => ['nullable', 'date', 'after:starts_at'],
            'is_active'   => ['required', 'boolean'],
        ];
    }
}