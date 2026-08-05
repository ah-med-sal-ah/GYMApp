<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\Gym;

class ExpensePolicy
{
    /**
     * Determine whether the gym can view any expenses.
     */
    public function viewAny(Gym $gym): bool
    {
        return true;
    }

    /**
     * Determine whether the gym can view the expense.
     */
    public function view(Gym $gym, Expense $expense): bool
    {
        return $gym->id === $expense->gym_id;
    }

    /**
     * Determine whether the gym can create expenses.
     */
    public function create(Gym $gym): bool
    {
        return true;
    }

    /**
     * Determine whether the gym can update the expense.
     */
    public function update(Gym $gym, Expense $expense): bool
    {
        return $gym->id === $expense->gym_id;
    }

    /**
     * Determine whether the gym can delete the expense.
     */
    public function delete(Gym $gym, Expense $expense): bool
    {
        return $gym->id === $expense->gym_id;
    }
}
