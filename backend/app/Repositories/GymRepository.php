<?php

namespace App\Repositories;

use App\Models\Gym;
use App\Repositories\Contracts\GymRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class GymRepository implements GymRepositoryInterface
{
    private const SORTABLE_COLUMNS = ['name', 'username', 'created_at'];

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Gym::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE_COLUMNS, true)
            ? $filters['sort_by']
            : 'created_at';
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->paginate($perPage);
    }

    public function find(int $id): Gym
    {
        return Gym::findOrFail($id);
    }

    public function updateProfile(Gym $gym, array $attributes): Gym
    {
        $gym->update($attributes);

        return $gym->fresh();
    }
}
