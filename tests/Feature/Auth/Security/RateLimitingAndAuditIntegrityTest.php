<?php

declare(strict_types=1);

use App\Models\Users\Admin;
use App\Models\Users\AdminAccessLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = Admin::create([
        'id' => (string) Str::uuid(),
        'first_name' => 'Target',
        'last_name' => 'Account',
        'email' => 'target@security.com',
        'password' => Hash::make('SafePassword123!'),
        'is_active' => true,
    ]);
});

test('falsificacion de ip via x-forwarded-for no evade el rate limiting dinamico', function (): void {
    for ($i = 1; $i <= 5; $i++) {
        $this->withHeaders(['X-Forwarded-For' => "10.0.0.{$i}"])
            ->post(route('admin.login.store'), [
                'email' => 'target@security.com',
                'password' => 'WrongPassword',
            ]);
    }

    $response = $this->withHeaders(['X-Forwarded-For' => '10.0.0.99'])
        ->post(route('admin.login.store'), [
            'email' => 'target@security.com',
            'password' => 'WrongPassword',
        ]);

    $response->assertSessionHasErrors('email');
    
    $this->assertDatabaseHas('admin_access_logs', [
        'email_attempted' => 'target@security.com',
        'status' => 'RATE_LIMITED',
    ]);
});