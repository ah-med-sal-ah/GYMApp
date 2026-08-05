<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Gym;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
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
            'title' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'amount' => fake()->randomFloat(2, 10, 2000),
            'expense_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'category' => fake()->randomElement(Expense::CATEGORIES),
        ];
    }
}
