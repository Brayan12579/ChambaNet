<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define los datos predeterminados del modelo para las pruebas.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_offer_id' => JobOffer::factory(),
            'user_id' => User::factory()->candidate(),
            'cv_path' => 'applications/test-cv.pdf',
            'cover_letter' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
