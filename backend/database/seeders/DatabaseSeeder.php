<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'username' => 'admin',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->call([
            GymSeeder::class,
            SportSeeder::class,
            CoachSeeder::class,
            ClientSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
