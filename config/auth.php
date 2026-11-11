<?php

use App\Models\User;

return [

    /* Opciones predeterminadas para iniciar sesión y recuperar contraseñas. */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /* Mecanismos de autenticación y proveedor de usuarios asociado. */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /* Proveedores que consultan los usuarios en la base de datos. */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

    ],

    /* Recuperación de contraseñas: almacenamiento, vencimiento en minutos y espera en segundos entre solicitudes. */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /* Tiempo en segundos antes de solicitar nuevamente la confirmación de contraseña. */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
