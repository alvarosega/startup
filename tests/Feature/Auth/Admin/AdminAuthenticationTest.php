<?php

declare(strict_types=1);

use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'super_admin']);

    $this->adminPassword = 'AdminPassword123!';
    
    $this->admin = Admin::create([
        'id'         => (string) Str::uuid(),
        'first_name' => 'Super',
        'last_name'  => 'Admin',
        'email'      => 'admin@empresa.com',
        'password'   => $this->adminPassword,
        'is_active'  => true,
    ]);

    $this->admin->assignRole('super_admin');
});

test('admin puede autenticarse con credenciales correctas y regenera sesion', function (): void {
    $response = $this->post(route('admin.login.store'), [
        'email'    => 'admin@empresa.com',
        'password' => $this->adminPassword,
    ]);

    $response->assertRedirect(route('dashboard.index'));
    $this->assertAuthenticatedAs($this->admin, 'super_admin');
});

test('admin inactivo no puede iniciar sesion y se rechaza la autenticacion', function (): void {
    $this->admin->update(['is_active' => false]);

    $response = $this->post(route('admin.login.store'), [
        'email'    => 'admin@empresa.com',
        'password' => $this->adminPassword,
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest('super_admin');
});

test('logout destruye sesion, invalida token csrf y redirige a login', function (): void {
    $this->actingAs($this->admin, 'super_admin');

    $response = $this->post(route('admin.logout'));

    $response->assertRedirect(route('admin.login'));
    $this->assertGuest('super_admin');
});

test('invitado autenticado como admin no puede acceder nuevamente a login', function (): void {
    $this->actingAs($this->admin, 'super_admin');

    $response = $this->get(route('admin.login'));

    $response->assertRedirect(route('dashboard.index'));
});