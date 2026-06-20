<?php

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\ResetPasswordData;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction
{
    public function execute(ResetPasswordData $data): void
    {
        $record = \Illuminate\Support\Facades\DB::table('password_reset_codes_drivers')
            ->where('email', $data->email)
            ->first();
    
        if (!$record || !\Illuminate\Support\Facades\Hash::check($data->code, $record->token) || 
            \Carbon\Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'code' => 'Código inválido o expirado.',
            ]);
        }
    
        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $driver = Driver::where('email', $data->email)->firstOrFail();
            $driver->update(['password' => $data->password]);
            \Illuminate\Support\Facades\DB::table('password_reset_codes_drivers')->where('email', $data->email)->delete();
        });
    }
}