<?php

namespace App\Policies;

use App\Models\Gym;
use App\Models\User;

class GymPolicy
{
    /**
     * Determine whether the admin can view the list of gyms.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the admin can view the gym.
     */
    public function view(User $user, Gym $gym): bool
    {
        return true;
    }
}
