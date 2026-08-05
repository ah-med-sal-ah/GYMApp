<?php

namespace App\Policies;

use App\Models\Coach;
use App\Models\Gym;

class CoachPolicy
{
    /**
     * Determine whether the gym can view any coaches.
     */
    public function viewAny(Gym $gym): bool
    {
        return true;
    }

    /**
     * Determine whether the gym can view the coach.
     */
    public function view(Gym $gym, Coach $coach): bool
    {
        return $gym->id === $coach->gym_id;
    }

    /**
     * Determine whether the gym can create coaches.
     */
    public function create(Gym $gym): bool
    {
        return true;
    }

    /**
     * Determine whether the gym can update the coach.
     */
    public function update(Gym $gym, Coach $coach): bool
    {
        return $gym->id === $coach->gym_id;
    }

    /**
     * Determine whether the gym can delete the coach.
     */
    public function delete(Gym $gym, Coach $coach): bool
    {
        return $gym->id === $coach->gym_id;
    }
}
