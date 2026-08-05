<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Coach;
use App\Models\Gym;
use App\Models\Sport;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
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

        Client::factory()
            ->count(20)
            ->for($gym)
            ->create()
            ->each(function (Client $client) use ($sportIds, $gym) {
                $clientSportIds = $sportIds->random(random_int(1, min(3, $sportIds->count())));
                $client->sports()->sync($clientSportIds->toArray());

                $coach = Coach::where('gym_id', $gym->id)
                    ->whereIn('sport_id', $clientSportIds)
                    ->inRandomOrder()
                    ->first();

                if ($coach) {
                    $client->update(['coach_id' => $coach->id]);
                }
            });
    }
}
