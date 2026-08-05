<?php

namespace App\Services;

use App\Models\Coach;
use App\Models\Gym;
use App\Repositories\Contracts\CoachRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class CoachService
{
    private const PHOTO_DIRECTORY = 'coaches';

    public function __construct(
        private readonly CoachRepositoryInterface $coaches,
    ) {}

    public function list(Gym $gym, array $filters): LengthAwarePaginator
    {
        return $this->coaches->paginate($gym, $filters);
    }

    public function find(Gym $gym, int $id): Coach
    {
        return $this->coaches->findForGym($gym, $id);
    }

    public function findBySport(Gym $gym, int $sportId): Collection
    {
        return $this->coaches->findBySport($gym, $sportId);
    }

    public function create(Gym $gym, array $data, ?UploadedFile $photo): Coach
    {
        $attributes = [
            'gym_id' => $gym->id,
            'sport_id' => $data['sport_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'age' => $data['age'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'salary' => $data['salary'],
            'photo' => $photo ? $this->storePhoto($photo) : null,
        ];

        return $this->coaches->create($attributes);
    }

    public function update(Coach $coach, array $data, ?UploadedFile $photo): Coach
    {
        $attributes = array_intersect_key($data, array_flip([
            'first_name', 'last_name', 'age', 'email', 'phone', 'salary', 'sport_id',
        ]));

        if ($photo) {
            $this->deletePhoto($coach->photo);
            $attributes['photo'] = $this->storePhoto($photo);
        }

        return $this->coaches->update($coach, $attributes);
    }

    public function delete(Coach $coach): void
    {
        $this->coaches->delete($coach);
    }

    private function storePhoto(UploadedFile $photo): string
    {
        return $photo->store(self::PHOTO_DIRECTORY, 'public');
    }

    private function deletePhoto(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
