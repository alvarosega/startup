<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Profiles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        // Validación estricta de mayoría de edad (18 años atrás desde la fecha actual en 2026)
        $maxBirthDate = Carbon::now()->subYears(18)->format('Y-m-d');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', "before_or_equal:{$maxBirthDate}"],
            'gender'     => ['required', 'string', 'in:MALE,FEMALE,OTHER'],
        ];
    }
}