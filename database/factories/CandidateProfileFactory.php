<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateProfile>
 */
class CandidateProfileFactory extends Factory
{
    /**
     * Define los datos predeterminados del modelo para las pruebas.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->candidate(),
            'district_id' => District::factory(),
            'phone' => '9'.fake()->numerify('########'),
            'bio' => fake()->sentence(),
            'experience' => fake()->paragraph(),
        ];
    }
}
