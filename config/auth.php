<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | El guard predeterminado del sistema se establece en 'customer' para
    | proteger la plataforma pública por defecto.
    |
    */

    'defaults' => [
        'guard' => 'customer',
        'passwords' => 'customers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Definición de los 3 guards basados en sesiones tradicionales.
    | Comparten el dominio físico pero actúan bajo identificadores lógicos independientes.
    |
    */

    'guards' => [
        'super_admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
        'driver' => [
            'driver' => 'session',
            'provider' => 'drivers',
        ],
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Mapeo directo a los modelos Eloquent de los tres silos independientes.
    | All models implement Authenticatable y usan identificadores UUID.
    |
    */

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
        'drivers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Driver::class,
        ],
        'customers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Customer::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | REGLA DE NEGOCIO: Se elimina por completo el broker 'super_admin'.
    | El silo administrativo no tiene permitido el restablecimiento de contraseñas.
    |
    */

    'passwords' => [
        'customers' => [
            'provider' => 'customers',
            'table' => 'password_reset_codes_customers',
            'expire' => 60,
            'throttle' => 60,
        ],
        'drivers' => [
            'provider' => 'drivers',
            'table' => 'password_reset_codes_drivers',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];