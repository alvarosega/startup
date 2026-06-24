<?php

use App\Models\Users\Admin;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configuración del entorno aislado de Spatie Roles con el guard específico de la configuración
    Role::create(['name' => 'super_admin', 'guard_name' => 'super_admin']);
    
    // Garantizar limpieza del Rate Limiter antes de cada escenario
    RateLimiter::clear(Str::transliterate(Str::lower('admin@example.com').'|127.0.0.1'));
});

/*
|--------------------------------------------------------------------------
| Escenarios de Éxito
|--------------------------------------------------------------------------
*/

test('un super admin activo con credenciales correctas puede autenticarse, muta la persistencia y es redirigido al dashboard', function () {
    $password = 'AdminSecurePassword2026!';
    $uuid = Str::uuid()->toString();

    $admin = Admin::factory()->create([
        'id' => $uuid,
        'first_name' => 'Alexander',
        'last_name' => 'Pierce',
        'phone' => '+51999999999',
        'branch_id' => null,
        'email' => 'admin@example.com',
        'password' => Hash::make($password),
        'is_active' => true,
        'last_login_at' => null,
        'last_seen_at' => null,
    ]);
    
    $admin->assignRole('super_admin');

    $response = $this->post(route('login.store'), [
        'email' => 'admin@example.com',
        'password' => $password,
    ]);

    // Verificación de redirección a la ruta de éxito estipulada
    $response->assertStatus(302);
    $response->assertRedirect(route('dashboard.index'));
    
    // Verificación del estado de autenticación en el Guard específico del dominio administrativo
    $this->assertAuthenticatedAs($admin, 'super_admin');

    // Verificación matemática y de mutación de la persistencia obligatoria en MySQL
    $admin->refresh();
    expect($admin->last_login_at)->not->toBeNull()
        ->and($admin->last_seen_at)->not->toBeNull();
    
    $this->assertDatabaseHas('admins', [
        'id' => $uuid,
        'email' => 'admin@example.com',
        'is_active' => true,
    ]);
});

/*
|--------------------------------------------------------------------------
| Escenarios de Seguridad y Mitigación de Enumeración (Caja Negra)
|--------------------------------------------------------------------------
*/

test('la autenticacion falla con contraseña incorrecta y devuelve error generico auth.failed para mitigar ataques', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('PasswordCorrecta123!'),
        'is_active' => true,
    ]);
    $admin->assignRole('super_admin');

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'admin@example.com',
        'password' => 'PasswordIncorrecta999!',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => trans('auth.failed'),
    ]);
    
    $this->assertGuest('super_admin');
});

test('la autenticacion falla con correo inexistente y devuelve el mismo error generico para evitar enumeracion de cuentas', function () {
    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'nonexistent_user_2026@example.com',
        'password' => 'AnyPassword123!',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => trans('auth.failed'),
    ]);
    
    $this->assertGuest('super_admin');
});

test('un super admin inactivo tiene el acceso denegado y se le aplica la misma respuesta de error generico', function () {
    $password = 'AdminSecurePassword2026!';
    $admin = Admin::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make($password),
        'is_active' => false,
    ]);
    $admin->assignRole('super_admin');

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'admin@example.com',
        'password' => $password,
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => trans('auth.failed'),
    ]);
    
    $this->assertGuest('super_admin');
});

test('un usuario con credenciales correctas pero sin el rol super_admin tiene el acceso denegado bajo el mismo error', function () {
    $password = 'AdminSecurePassword2026!';
    $admin = Admin::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make($password),
        'is_active' => true,
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'admin@example.com',
        'password' => $password,
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => trans('auth.failed'),
    ]);
    
    $this->assertGuest('super_admin');
});

/*
|--------------------------------------------------------------------------
| Cobertura de Validaciones Estrictas del Contrato Técnico (FormRequest)
|--------------------------------------------------------------------------
*/

dataset('payloads_invalidos', [
    'email ausente/vacío'   => [['email' => '', 'password' => 'Password123!'], 'email'],
    'password ausente/vacío'=> [['email' => 'admin@example.com', 'password' => ''], 'password'],
    'formato email inválido' => [['email' => 'mal_formato_email.com', 'password' => 'Password123!'], 'email'],
]);

test('el contrato tecnico del login valida estrictamente los tipos de datos y nulidades', function (array $payload, string $errorKey) {
    $response = $this->from(route('login'))->post(route('login.store'), $payload);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors($errorKey);
    $this->assertGuest('super_admin');
})->with('payloads_invalidos');

/*
|--------------------------------------------------------------------------
| Pruebas de Inyección y Robustez de Seguridad (SQL Injection / XSS Mitigation)
|--------------------------------------------------------------------------
*/

dataset('payloads_maliciosos_seguridad', [
    'sql_injection_auth_bypass_email'    => ["admin@example.com' OR '1'='1", "Password123!"],
    'sql_injection_auth_bypass_password' => ["admin@example.com", "' OR 1=1 --"],
    'sql_blind_injection_sleep'          => ["admin@example.com' AND SLEEP(5) --", "Password123!"],
    'xss_injection_payload'              => ["<script>alert('xss_attack')</script>@example.com", "Password123!"],
]);

test('el sistema procesa de forma segura los payloads maliciosos de inyeccion sin romper la integridad', function (string $email, string $password) {
    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $email,
        'password' => $password,
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['email']);
    $this->assertGuest('super_admin');
})->with('payloads_maliciosos_seguridad');

/*
|--------------------------------------------------------------------------
| Control y Simulación de Rate Limiting (Protección contra Fuerza Bruta)
|--------------------------------------------------------------------------
*/

test('el endpoint bloquea de forma estricta las peticiones excesivas tras cumplirse el quinto intento fallido', function () {
    $email = 'brute_force_attack@example.com';
    $password = 'WrongAttempt2026!';

    // Simulación consecutiva de los 5 intentos permitidos por el contrato del FormRequest
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.store'), [
            'email' => $email,
            'password' => $password,
        ]);
    }

    // El sexto intento debe lanzar de manera mandatoria el bloqueo por Rate Limiter
    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $email,
        'password' => $password,
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
    
    // Verificación de que la sesión contiene el error de throttle inyectado por ValidationException
    $response->assertSessionHasErrors('email');
    $errors = session('errors')->get('email');
    
    expect($errors[0])->toContain(trans('auth.throttle', ['seconds' => 60, 'minutes' => 1]));
});