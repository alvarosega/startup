<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Actions\Customer\Auth\LoginCustomerAction;
use App\DTOs\Customer\Auth\LoginCustomerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Customer/Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request, LoginCustomerAction $action): RedirectResponse
    {
        $dto = LoginCustomerData::fromArray($request->validated());
        $result = $action->execute($dto);

        if (!$result->isSuccess) {
            return back()->withErrors(['phone' => $result->errorMessage])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended(route('customer.index'));
    }
}