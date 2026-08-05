<?php

namespace App\Repositories\Contracts;

use App\Models\Gym;
use App\Models\Sport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SportRepositoryInterface
{
    public function paginate(Gym $gym, array $filters): LengthAwarePaginator;

    public function findForGym(Gym $gym, int $id): Sport;

    public function create(array $attributes): Sport;

    public function update(Sport $sport, array $attributes): Sport;

    public function delete(Sport $sport): void;

    public function allForGym(Gym $gym): Collection;
}
