<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Models\Users\Customer;
use Illuminate\Support\Facades\Auth;

final class EstablishCustomerSessionAction
{
    /**
     * Ejecuta la autenticación de la sesión web del cliente de manera aislada.
     */
    public function execute(Customer $customer): void
    {
        Auth::guard('customer')->login($customer);
        request()->session()->regenerate();
    }
}