<?php

namespace App\Services;

use App\Models\Gym;
use App\Models\Sport;
use App\Repositories\Contracts\SportRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class SportService
{
    private const PHOTO_DIRECTORY = 'sports';

    public function __construct(
        private readonly SportRepositoryInterface $sports,
    ) {}

    public function list(Gym $gym, array $filters): LengthAwarePaginator
    {
        return $this->sports->paginate($gym, $filters);
    }

    public function find(Gym $gym, int $id): Sport
    {
        return $this->sports->findForGym($gym, $id);
    }

    public function create(Gym $gym, array $data, ?UploadedFile $photo): Sport
    {
        $attributes = [
            'gym_id' => $gym->id,
            'name' => $data['name'],
            'day_price' => $data['day_price'] ?? null,
            'week_price' => $data['week_price'] ?? null,
            'month_price' => $data['month_price'] ?? null,
            'year_price' => $data['year_price'] ?? null,
            'photo' => $photo ? $this->storePhoto($photo) : null,
        ];

        return $this->sports->create($attributes);
    }

    public function update(Sport $sport, array $data, ?UploadedFile $photo): Sport
    {
        $attributes = array_intersect_key($data, array_flip([
            'name', 'day_price', 'week_price', 'month_price', 'year_price',
        ]));

        if ($photo) {
            $this->deletePhoto($sport->photo);
            $attributes['photo'] = $this->storePhoto($photo);
        }

        return $this->sports->update($sport, $attributes);
    }

    public function delete(Sport $sport): void
    {
        $this->sports->delete($sport);
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
