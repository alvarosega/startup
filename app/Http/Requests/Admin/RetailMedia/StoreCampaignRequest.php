<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\RetailMedia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'provider_id' => ['required', 'uuid', 'exists:providers,id'],
            'name'        => ['required', 'string', 'max:128'],
            'type'        => ['required', 'string', Rule::in(['PAID', 'INTERNAL'])],
            'starts_at'   => ['nullable', 'date'],
            'ends_at'     => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active'   => ['required', 'boolean'],
        ];
    }
}