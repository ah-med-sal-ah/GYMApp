<?php

namespace Database\Factories;

use App\Models\Coach;
use App\Models\Gym;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coach>
 */
class CoachFactory extends Factory
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
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'age' => fake()->numberBetween(18, 80),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'salary' => fake()->randomFloat(2, 1500, 6000),
        ];
    }
}
