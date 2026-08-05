<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sport>
 */
class SportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gym_id' => Gym::factory(),
            'photo' => null,
            'name' => fake()->unique()->words(2, true),
            'day_price' => fake()->randomFloat(2, 5, 15),
            'week_price' => fake()->randomFloat(2, 20, 40),
            'month_price' => fake()->randomFloat(2, 40, 150),
            'year_price' => fake()->randomFloat(2, 400, 1500),
        ];
    }
}
