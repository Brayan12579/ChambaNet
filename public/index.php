<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Comprueba si la aplicación está en modo de mantenimiento.
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Carga automáticamente las dependencias de Composer.
require __DIR__.'/../vendor/autoload.php';

// Inicializa Laravel y procesa la solicitud.
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
