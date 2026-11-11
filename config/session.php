<?php

use Illuminate\Support\Str;

return [

    /* Almacenamiento predeterminado de las sesiones de los usuarios. */

    'driver' => env('SESSION_DRIVER', 'database'),

    /* Duración de la sesión inactiva en minutos y opción de cerrarla al salir del navegador. */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /* Cifrado de los datos de la sesión antes de almacenarlos. */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /* Directorio para las sesiones cuando se guardan en archivos. */

    'files' => storage_path('framework/sessions'),

    /* Conexión utilizada al guardar sesiones en base de datos o Redis. */

    'connection' => env('SESSION_CONNECTION'),

    /* Tabla utilizada para guardar las sesiones en la base de datos. */

    'table' => env('SESSION_TABLE', 'sessions'),

    /* Almacén de caché utilizado por los controladores de sesión que lo requieren. */

    'store' => env('SESSION_STORE'),

    /* Probabilidad de eliminar sesiones vencidas en cada solicitud. */

    'lottery' => [2, 100],

    /* Nombre de la cookie que identifica la sesión del usuario. */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /* Ruta en la que el navegador enviará la cookie de sesión. */

    'path' => env('SESSION_PATH', '/'),

    /* Dominio y subdominios que pueden recibir la cookie de sesión. */

    'domain' => env('SESSION_DOMAIN'),

    /* Permite enviar la cookie de sesión únicamente mediante HTTPS. */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /* Impide que JavaScript acceda a la cookie de sesión. */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /* Política de envío de la cookie en solicitudes entre sitios para ayudar a prevenir ataques CSRF. */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /* Vincula la cookie al sitio principal cuando se utiliza en un contexto entre sitios. */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

    /* Formato utilizado para serializar los datos de la sesión. */

    'serialization' => 'json',

];
