<?php

namespace Database\Seeders;

use App\Models\Coach;
use App\Models\Gym;
use App\Models\Sport;
use Illuminate\Database\Seeder;

class CoachSeeder extends Seeder
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

        $sportIds = Sport::where('gym_id', $gym->id)->pluck('id');

        if ($sportIds->isEmpty()) {
            return;
        }

        Coach::factory()
            ->count(5)
            ->for($gym)
            ->create()
            ->each(function (Coach $coach) use ($sportIds) {
                $coach->update(['sport_id' => $sportIds->random()]);
            });
    }
}
