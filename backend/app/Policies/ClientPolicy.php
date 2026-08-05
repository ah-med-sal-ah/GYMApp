<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Gym;

class ClientPolicy
{
    /**
     * Determine whether the gym can view any clients.
     */
    public function viewAny(Gym $gym): bool
    {
        return true;
    }

    /**
     * Determine whether the gym can view the client.
     */
    public function view(Gym $gym, Client $client): bool
    {
        return $gym->id === $client->gym_id;
    }

    /**
     * Determine whether the gym can create clients.
     */
    public function create(Gym $gym): bool
    {
        return true;
    }

    /**
     * Determine whether the gym can update the client.
     */
    public function update(Gym $gym, Client $client): bool
    {
        return $gym->id === $client->gym_id;
    }

    /**
     * Determine whether the gym can delete the client.
     */
    public function delete(Gym $gym, Client $client): bool
    {
        return $gym->id === $client->gym_id;
    }
}
