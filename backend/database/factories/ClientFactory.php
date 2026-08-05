<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Gym;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $registrationType = fake()->randomElement(['day', 'week', 'month', 'year']);
        $registrationStart = Carbon::today();
        $registrationEnd = match ($registrationType) {
            'day' => $registrationStart->copy()->addDay(),
            'week' => $registrationStart->copy()->addWeek(),
            'month' => $registrationStart->copy()->addMonth(),
            'year' => $registrationStart->copy()->addYear(),
        };

        return [
            'gym_id' => Gym::factory(),
            'photo' => null,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'age' => fake()->numberBetween(16, 65),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'registration_type' => $registrationType,
            'registration_start' => $registrationStart,
            'registration_end' => $registrationEnd,
        ];
    }
}
