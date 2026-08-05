<?php

namespace App\Policies;

use App\Models\Gym;
use App\Models\Sport;

class SportPolicy
{
    /**
     * Determine whether the gym can view any sports.
     */
    public function viewAny(Gym $gym): bool
    {
        return true;
    }

    /**
     * Determine whether the gym can view the sport.
     */
    public function view(Gym $gym, Sport $sport): bool
    {
        return $gym->id === $sport->gym_id;
    }

    /**
     * Determine whether the gym can create sports.
     */
    public function create(Gym $gym): bool
    {
        return true;
    }

    /**
     * Determine whether the gym can update the sport.
     */
    public function update(Gym $gym, Sport $sport): bool
    {
        return $gym->id === $sport->gym_id;
    }

    /**
     * Determine whether the gym can delete the sport.
     */
    public function delete(Gym $gym, Sport $sport): bool
    {
        return $gym->id === $sport->gym_id;
    }
}
