<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Actions\Customer\Auth\EstablishCustomerSessionAction;
use App\Actions\Customer\Auth\PrepareRegisterViewAction;
use App\Actions\Customer\Auth\RegisterCustomerAction;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\Models\Users\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class RegisterController extends Controller
{
    public function create(Request $request, PrepareRegisterViewAction $action): Response
    {
        return Inertia::render('Customer/Auth/Register', $action->execute($request->query()));
    }

    public function store(
        RegisterRequest $request,
        RegisterCustomerAction $registerAction,
        EstablishCustomerSessionAction $sessionAction
    ): RedirectResponse {
        $result = $registerAction->execute(
            RegisterCustomerData::fromArray($request->validated()),
            $request->header('X-Idempotency-Key')
        );

        if (!$result->isSuccess) {
            return back()->withErrors(['phone' => $result->errorMessage])->withInput();
        }

        /** @var Customer $customer */
        $customer = $result->data;
        
        $sessionAction->execute($customer);

        return redirect()->route('customer.index')
            ->with('status', 'Registro completado con éxito.');
    }
}