<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
use App\Models\District;
use App\Models\JobOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobOffer>
 */
class JobOfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'category_id' => Category::factory(),
            'district_id' => District::factory(),
            'title' => 'Asistente de atención al cliente',
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'requirements' => 'Responsabilidad, puntualidad y disposición para aprender.',
            'conditions' => 'Horario acordado y condiciones informadas antes de contratar.',
            'schedule' => 'full_time',
            'contract_type' => 'temporary',
            'salary_min' => 1300,
            'salary_max' => 1600,
            'vacancies' => 2,
            'expires_at' => today()->addMonth(),
            'status' => 'published',
            'featured' => false,
            'is_demo' => false,
        ];
    }
}
