<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
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

        // name => [day, week, month, year]
        $sports = [
            'Football' => [5, 30, 80, 800],
            'Basketball' => [5, 30, 80, 800],
            'Swimming' => [7, 40, 100, 1000],
            'Yoga' => [4, 25, 60, 600],
            'CrossFit' => [10, 55, 120, 1200],
        ];

        foreach ($sports as $name => [$dayPrice, $weekPrice, $monthPrice, $yearPrice]) {
            Sport::firstOrCreate(
                ['gym_id' => $gym->id, 'name' => $name],
                [
                    'day_price' => $dayPrice,
                    'week_price' => $weekPrice,
                    'month_price' => $monthPrice,
                    'year_price' => $yearPrice,
                ],
            );
        }
    }
}
