<?php

namespace App\Repositories\Contracts;

use App\Models\Expense;
use App\Models\Gym;
use Illuminate\Pagination\LengthAwarePaginator;

interface ExpenseRepositoryInterface
{
    public function paginate(Gym $gym, array $filters): LengthAwarePaginator;

    public function findForGym(Gym $gym, int $id): Expense;

    public function create(array $attributes): Expense;

    public function update(Expense $expense, array $attributes): Expense;

    public function delete(Expense $expense): void;

    public function sumForGym(Gym $gym): float;
}
