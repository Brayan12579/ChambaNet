<?php

namespace Database\Factories;

use App\Models\JobOffer;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_offer_id' => JobOffer::factory(),
            'user_id' => User::factory()->candidate(),
            'reason' => 'Información sospechosa',
            'details' => fake()->paragraph(),
            'status' => 'pending',
        ];
    }
}
