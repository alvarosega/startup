<?php

declare(strict_types=1);

use App\Models\Users\Admin;
use App\Models\Users\AdminAccessLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'super_admin']);

    $this->admin = Admin::create([
        'id'         => (string) Str::uuid(),
        'first_name' => 'Audit',
        'last_name'  => 'Tester',
        'email'      => 'auditor@empresa.com',
        'password'   => 'CorrectPassword123!',
        'is_active'  => true,
    ]);

    $this->admin->assignRole('super_admin');
});

test('bloqueo por rate limiter registra estado RATE_LIMITED en admin_access_logs', function (): void {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('admin.login.store'), [
            'email'    => 'auditor@empresa.com',
            'password' => 'WrongPassword',
        ]);
    }

    $response = $this->post(route('admin.login.store'), [
        'email'    => 'auditor@empresa.com',
        'password' => 'WrongPassword',
    ]);

    $response->assertSessionHasErrors('email');

    $this->assertDatabaseHas('admin_access_logs', [
        'email_attempted' => 'auditor@empresa.com',
        'status'          => 'RATE_LIMITED',
    ]);
});

test('ip real es registrada en access logs respetando headers seguros', function (): void {
    $clientIp = '190.181.45.10';

    for ($i = 0; $i < 6; $i++) {
        $this->withServerVariables(['REMOTE_ADDR' => $clientIp])
            ->post(route('admin.login.store'), [
                'email'    => 'auditor@empresa.com',
                'password' => 'WrongPassword',
            ]);
    }

    $this->assertDatabaseHas('admin_access_logs', [
        'email_attempted' => 'auditor@empresa.com',
        'ip_address'      => $clientIp,
        'status'          => 'RATE_LIMITED',
    ]);
});