<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Operations\Branch;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchSchedulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('super_admin')?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'schedules'               => ['required', 'array', 'size:7'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.is_closed'   => ['required', 'boolean'],
            'schedules.*.opening_time'=> ['required_if:schedules.*.is_closed,false', 'nullable', 'date_format:H:i'],
            'schedules.*.closing_time'=> ['required_if:schedules.*.is_closed,false', 'nullable', 'date_format:H:i', 'after:schedules.*.opening_time'],
        ];
    }
}