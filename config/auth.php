<?php

return [


    'defaults' => [
        'guard' => 'customer',
        'passwords' => 'customers',
    ],

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

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Users\Admin::class,
        ],
        'drivers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Users\Driver::class,
        ],
        'customers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Users\Customer::class,
        ],
    ],


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