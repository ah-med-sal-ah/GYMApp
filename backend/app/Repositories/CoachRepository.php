<?php

namespace App\Repositories;

use App\Models\Coach;
use App\Models\Gym;
use App\Repositories\Contracts\CoachRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CoachRepository implements CoachRepositoryInterface
{
    private const SORTABLE_COLUMNS = ['first_name', 'age', 'salary', 'created_at'];

    public function paginate(Gym $gym, array $filters): LengthAwarePaginator
    {
        $query = $gym->coaches()->with('sport');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['sport_id'])) {
            $query->where('sport_id', $filters['sport_id']);
        }

        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE_COLUMNS, true)
            ? $filters['sort_by']
            : 'created_at';
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->paginate($perPage);
    }

    public function findForGym(Gym $gym, int $id): Coach
    {
        return $gym->coaches()->with('sport')->findOrFail($id);
    }

    public function findBySport(Gym $gym, int $sportId): Collection
    {
        return $gym->coaches()->where('sport_id', $sportId)->get();
    }

    public function create(array $attributes): Coach
    {
        return Coach::create($attributes)->load('sport');
    }

    public function update(Coach $coach, array $attributes): Coach
    {
        $coach->update($attributes);

        return $coach->load('sport');
    }

    public function delete(Coach $coach): void
    {
        $coach->delete();
    }

    public function countForGym(Gym $gym): int
    {
        return $gym->coaches()->count();
    }

    public function sumSalariesForGym(Gym $gym): float
    {
        return (float) $gym->coaches()->sum('salary');
    }
}
