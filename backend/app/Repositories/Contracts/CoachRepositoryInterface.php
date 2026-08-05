<?php

namespace App\Repositories\Contracts;

use App\Models\Coach;
use App\Models\Gym;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CoachRepositoryInterface
{
    public function paginate(Gym $gym, array $filters): LengthAwarePaginator;

    public function findForGym(Gym $gym, int $id): Coach;

    public function findBySport(Gym $gym, int $sportId): Collection;

    public function create(array $attributes): Coach;

    public function update(Coach $coach, array $attributes): Coach;

    public function delete(Coach $coach): void;

    public function countForGym(Gym $gym): int;

    public function sumSalariesForGym(Gym $gym): float;
}
