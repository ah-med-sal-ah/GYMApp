<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Gym;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function list(Gym $gym, array $filters): LengthAwarePaginator
    {
        return $this->expenses->paginate($gym, $filters);
    }

    public function find(Gym $gym, int $id): Expense
    {
        return $this->expenses->findForGym($gym, $id);
    }

    public function create(Gym $gym, array $data): Expense
    {
        $attributes = [
            'gym_id' => $gym->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'] ?? Carbon::today(),
            'category' => $data['category'],
        ];

        return $this->expenses->create($attributes);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $attributes = array_intersect_key($data, array_flip([
            'title', 'description', 'amount', 'expense_date', 'category',
        ]));

        return $this->expenses->update($expense, $attributes);
    }

    public function delete(Expense $expense): void
    {
        $this->expenses->delete($expense);
    }
}
