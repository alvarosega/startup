<?php

declare(strict_types=1);

use App\Models\Operations\Branch;
use App\Models\Users\Customer;
use App\Actions\Customer\Cart\SyncGuestCartAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->branch = Branch::factory()->create([
        'id' => Str::uuid7()->toString(),
        'is_active' => true,
        'is_default' => true 
    ]);

    $this->customer = Customer::factory()->create([
        'branch_id' => $this->branch->id,
        'phone' => '+59170000000',
        'email' => 'customer@b2c.com',
        'password' => Hash::make('Secret123*'),
        'is_active' => true,
        'deleted_epoch' => 0,
    ]);

    $this->syncCartMock = mock(SyncGuestCartAction::class);
    $this->app->instance(SyncGuestCartAction::class, $this->syncCartMock);
});

it('autentica correctamente a un cliente activo y unifica el carrito', function () {
    session(['guest_client_uuid' => (string) Str::uuid()]);

    $this->syncCartMock
        ->shouldReceive('execute')
        ->once()
        ->with((string) $this->customer->id, session('guest_client_uuid'))
        ->andReturn(true);

    $response = $this->post(route('customer.login.store'), [
        'phone' => '+59170000000', // CORRECCIÓN: Consistencia internacional directa
        'password' => 'Secret123*',
        'remember' => true,
    ]);

    $response->assertRedirect(route('customer.index'));
    expect(Auth::guard('customer')->check())->toBeTrue();
    expect(Auth::guard('customer')->id())->toBe($this->customer->id);
});

it('falla la autenticacion con credenciales erroneas e incrementa el rate limiter', function () {
    $this->syncCartMock->shouldReceive('execute')->never();

    $response = $this->post(route('customer.login.store'), [
        'phone' => '+59170000000',
        'password' => 'WrongPassword',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['phone']);
    expect(Auth::guard('customer')->check())->toBeFalse();
});

it('bloquea el acceso por estrangulamiento tras superar el limite de intentos', function () {
    $this->syncCartMock->shouldReceive('execute')->never();
    
    $throttleKey = Str::transliterate('+59170000000|' . request()->ip());
    RateLimiter::clear($throttleKey);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('customer.login.store'), [
            'phone' => '+59170000000',
            'password' => 'WrongPassword',
        ]);
    }

    $response = $this->post(route('customer.login.store'), [
        'phone' => '+59170000000',
        'password' => 'WrongPassword',
    ]);

    $response->assertSessionHasErrors(['phone']);
    $errors = session('errors')->get('phone');
    // CORRECCIÓN: Evaluación por expresión regular para tolerar variaciones de sincronía (59 o 60 segundos)
    expect($errors[0])->toMatch('/Please try again in (59|60) seconds\./');
});

it('destruye la sesion de forma segura al hacer logout', function () {
    Auth::guard('customer')->login($this->customer);

    $response = $this->delete(route('customer.login.destroy'));

    $response->assertRedirect(route('customer.index'));
    expect(Auth::guard('customer')->check())->toBeFalse();
});