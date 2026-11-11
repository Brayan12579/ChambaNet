<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Contraseña utilizada por el generador de datos de prueba.
     */
    protected static ?string $password;

    /**
     * Define los datos predeterminados del modelo para las pruebas.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'candidate',
            'active' => true,
            'is_demo' => false,
        ];
    }

    /**
     * Indica que el correo electrónico del modelo aún no está verificado.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function candidate(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'candidate']);
    }

    public function employer(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'employer']);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'admin']);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }
}
