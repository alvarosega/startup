<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Operations\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBranchScheduleExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('super_admin');
        if (!$user) {
            return false;
        }
        if ($user->hasRole('super_admin')) {
            return true;
        }
        if (!$user->can('manage_branch_schedules')) {
            return false;
        }

        $branch = $this->route('branch');
        $branchId = $branch instanceof \App\Models\Operations\Branch ? $branch->id : $branch;

        return $user->branch_id === $branchId;
    }

    public function rules(): array
    {
        /** @var \App\Models\Operations\Branch $branch */
        $branch =$this->route('branch');

        return [
            'date'         => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::unique('branch_schedule_exceptions', 'date')->where('branch_id', $branch->id),
            ],
            'is_closed'    => ['required', 'boolean'],
            'opening_time' => ['required_if:is_closed,false', 'nullable', 'date_format:H:i'],
            'closing_time' => ['required_if:is_closed,false', 'nullable', 'date_format:H:i', 'after:opening_time'],
            'reason'       => ['nullable', 'string', 'max:255'],
        ];
    }
}