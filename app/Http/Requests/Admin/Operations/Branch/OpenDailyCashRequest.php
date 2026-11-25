<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Operations\Branch;

use Illuminate\Foundation\Http\FormRequest;

class OpenDailyCashRequest extends FormRequest
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
        if (!$user->can('manage_branch_cash')) {
            return false;
        }

        $branch = $this->route('branch');
        $branchId = $branch instanceof \App\Models\Operations\Branch ? $branch->id : $branch;

        return $user->branch_id === $branchId;
    }

    public function rules(): array
    {
        return [
            'opening_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}