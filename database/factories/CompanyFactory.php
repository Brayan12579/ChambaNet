<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define los datos predeterminados del modelo para las pruebas.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->employer(),
            'district_id' => District::factory(),
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'sector' => 'Servicios',
            'address' => fake()->streetAddress(),
            'verified' => false,
            'is_demo' => false,
        ];
    }
}
