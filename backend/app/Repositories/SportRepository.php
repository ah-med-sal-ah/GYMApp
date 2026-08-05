<?php

namespace App\Repositories;

use App\Models\Gym;
use App\Models\Sport;
use App\Repositories\Contracts\SportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SportRepository implements SportRepositoryInterface
{
    private const SORTABLE_COLUMNS = ['name', 'day_price', 'week_price', 'month_price', 'year_price', 'created_at'];

    public function paginate(Gym $gym, array $filters): LengthAwarePaginator
    {
        $query = $gym->sports();

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE_COLUMNS, true)
            ? $filters['sort_by']
            : 'created_at';
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->paginate($perPage);
    }

    public function findForGym(Gym $gym, int $id): Sport
    {
        return $gym->sports()->withCount(['coaches', 'clients'])->findOrFail($id);
    }

    public function create(array $attributes): Sport
    {
        $sport = Sport::create($attributes);

        return $sport->loadCount(['coaches', 'clients']);
    }

    public function update(Sport $sport, array $attributes): Sport
    {
        $sport->update($attributes);

        return $sport->loadCount(['coaches', 'clients']);
    }

    public function delete(Sport $sport): void
    {
        $sport->delete();
    }

    public function allForGym(Gym $gym): Collection
    {
        return $gym->sports()->get();
    }
}
