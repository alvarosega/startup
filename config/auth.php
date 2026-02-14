<?php

return [

    // 1. DEFAULTS (Generalmente es 'web')
    'defaults' => [
        'guard' => 'web',           // Por defecto, somos 'web' (Customer)
        'passwords' => 'customers', // La recuperación por defecto busca en customers
    ],

    // 2. GUARDS (Aquí definimos los dos mundos)
    'guards' => [
        // El guard para usuarios normales (Tienda)
        'web' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],

        // --- ESTO ES LO QUE PROBABLEMENTE TE FALTA ---
        // El guard para el Administrador
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins', // <--- Apunta al provider de abajo
        ],
        'driver' => [
            'driver' => 'session',
            'provider' => 'drivers',
        ],
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers', // Este provider lo definimos abajo
        ],
    ],

    // 3. PROVIDERS (Cómo buscar en la DB)
    'providers' => [
        'customers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Customer::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],

        'drivers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Driver::class, // Asegúrate de tener este modelo o créalo
        ],
    ],

    // 4. PASSWORDS
    'passwords' => [
        'customers' => [
            'provider' => 'customers',
            'table' => 'password_reset_codes_customers', // Tabla específica
            'expire' => 60,
            'throttle' => 60,
        ],

        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_codes_admins',    // Tabla específica
            'expire' => 60,
            'throttle' => 60,
        ],

        'drivers' => [
            'provider' => 'drivers',
            'table' => 'password_reset_codes_drivers',   // Tabla específica
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];