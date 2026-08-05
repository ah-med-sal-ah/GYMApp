<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GymSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gym::firstOrCreate(
            ['email' => 'demo@gym.test'],
            [
                'name' => 'Demo Gym',
                'username' => 'demogym',
                'password' => Hash::make('password'),
            ],
        );
    }
}
