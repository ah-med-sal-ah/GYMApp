<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Models\Gym;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    private const SORTABLE_COLUMNS = ['title', 'amount', 'expense_date', 'created_at'];

    public function paginate(Gym $gym, array $filters): LengthAwarePaginator
    {
        $query = $gym->expenses();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE_COLUMNS, true)
            ? $filters['sort_by']
            : 'expense_date';
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->paginate($perPage);
    }

    public function findForGym(Gym $gym, int $id): Expense
    {
        return $gym->expenses()->findOrFail($id);
    }

    public function create(array $attributes): Expense
    {
        return Expense::create($attributes);
    }

    public function update(Expense $expense, array $attributes): Expense
    {
        $expense->update($attributes);

        return $expense;
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    public function sumForGym(Gym $gym): float
    {
        return (float) $gym->expenses()->sum('amount');
    }
}
