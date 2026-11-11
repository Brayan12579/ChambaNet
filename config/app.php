<?php

return [

    /* Nombre de la aplicación que aparece en avisos y otros elementos de la página. */

    'name' => env('APP_NAME', 'ChambaNet'),

    /* Entorno de ejecución definido en el archivo .env. */

    'env' => env('APP_ENV', 'production'),

    /* Depuración: muestra detalles de los errores cuando está activada. */

    'debug' => (bool) env('APP_DEBUG', false),

    /* Dirección principal usada para generar enlaces desde los comandos de Artisan. */

    'url' => env('APP_URL', 'http://localhost'),

    /* Zona horaria usada para las fechas y horas de la aplicación. */

    'timezone' => 'America/Lima',

    /* Idioma principal, idioma alternativo e idioma de los datos de prueba. */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /* Clave y algoritmo para cifrar los datos. Las claves anteriores permiten leer datos existentes. */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /* Control del modo de mantenimiento y almacenamiento de su estado. */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
