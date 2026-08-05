<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Gym;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gym = Gym::first();

        if (! $gym) {
            return;
        }

        Expense::factory()->count(15)->for($gym)->create();
    }
}
