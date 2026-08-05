<?php

namespace App\Repositories\Contracts;

use App\Models\Gym;
use Illuminate\Pagination\LengthAwarePaginator;

interface GymRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function find(int $id): Gym;

    public function updateProfile(Gym $gym, array $attributes): Gym;
}
