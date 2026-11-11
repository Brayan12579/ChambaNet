<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Carga los datos iniciales de la aplicación en la base de datos.
     */
    public function run(): void
    {
        $this->call(TrabajoGoDemoSeeder::class);
    }
}
