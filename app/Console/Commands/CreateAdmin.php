<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'chambanet:admin';

    protected $description = 'Crear una cuenta de administrador para ChambaNet';

    public function handle(): int
    {
        $email = 'admin@chambanet.com';
        $name = 'Administrador ChambaNet';

        if (!$this->input->isInteractive()) {
            $this->error('Ejecuta este comando en una terminal interactiva.');
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('Ya existe una cuenta con este correo.');
            return self::FAILURE;
        }

        $password = $this->secret('Introduce la contraseña del administrador (mínimo 12 caracteres)');
        $confirmation = $this->secret('Confirma la contraseña');

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:160'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        User::create([
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'role' => 'admin',
            'active' => true,
        ]);

        $this->info('Administrador creado correctamente.');
        $this->info('Correo: '.$email);

        return self::SUCCESS;
    }
}
