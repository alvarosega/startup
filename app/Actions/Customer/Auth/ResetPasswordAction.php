<?php

namespace App\Actions\Customer\Auth;

use App\Models\Customer;
use App\DTOs\Customer\Auth\ResetPasswordDTO; // <--- IMPORTAMOS EL DTO
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ResetPasswordAction 
{
    public function execute(ResetPasswordDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $record = DB::table('password_reset_codes_customers')
                ->where('email', $dto->email)
                ->first();

            if (!$record || !Hash::check($dto->code, $record->token) || Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
                throw ValidationException::withMessages([
                    'code' => 'El código es incorrecto o ha expirado.',
                ]);
            }
            $customer = Customer::where('email', $dto->email)->first();
            
            if (!$customer) {
                throw ValidationException::withMessages([
                    'email' => 'No se encontró el usuario.',
                ]);
            }

            $customer->update([
                'password' => Hash::make($dto->password)
            ]);

            DB::table('password_reset_codes_customers')->where('email', $dto->email)->delete();
        });
    }
}