<?php

namespace App\Services;

use App\Models\Gym;
use App\Repositories\Contracts\GymRepositoryInterface;

class ProfileService
{
    public function __construct(
        private readonly GymRepositoryInterface $gyms,
    ) {}

    public function update(Gym $gym, array $data): Gym
    {
        $attributes = array_intersect_key($data, array_flip(['email', 'phone']));

        return $this->gyms->updateProfile($gym, $attributes);
    }
}
