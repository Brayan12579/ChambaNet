<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PrepareTestDatabase extends Command
{
    protected $signature = 'chambanet:prepare-tests';

    protected $description = 'Crear y migrar exclusivamente la base MySQL aislada trabajogo_testing, sin borrar datos';

    public function handle(): int
    {
        if (! app()->environment('local', 'testing') || config('database.default') !== 'mysql') {
            $this->error('Este comando solo está disponible en local/testing con MySQL.');

            return self::FAILURE;
        }
        $connection = config('database.connections.mysql');
        $connection['url'] = null;
        $connection['database'] = null;
        config(['database.connections.test_provision' => $connection]);
        DB::connection('test_provision')->statement('CREATE DATABASE IF NOT EXISTS trabajogo_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        DB::disconnect('test_provision');

        config(['database.connections.mysql.database' => 'trabajogo_testing', 'database.connections.mysql.url' => null]);
        DB::purge('mysql');
        $exitCode = Artisan::call('migrate', ['--database' => 'mysql', '--force' => true, '--no-interaction' => true]);
        $this->output->write(Artisan::output());
        $this->info('Pruebas preparadas en trabajogo_testing. Ejecuta php artisan test.');

        return $exitCode;
    }
}
