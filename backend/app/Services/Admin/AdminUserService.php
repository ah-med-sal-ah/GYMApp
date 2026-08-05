<?php

namespace App\Services\Admin;

use App\Models\Gym;
use App\Repositories\Contracts\GymRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminUserService
{
    private const SORT_COLUMN_MAP = [
        'gym_name' => 'name',
        'username' => 'username',
        'created_at' => 'created_at',
    ];

    public function __construct(
        private readonly GymRepositoryInterface $gyms,
    ) {}

    public function list(array $filters): LengthAwarePaginator
    {
        if (isset($filters['sort_by'])) {
            $filters['sort_by'] = self::SORT_COLUMN_MAP[$filters['sort_by']] ?? null;
        }

        return $this->gyms->paginate($filters);
    }

    public function find(int $id): Gym
    {
        return $this->gyms->find($id);
    }
}
