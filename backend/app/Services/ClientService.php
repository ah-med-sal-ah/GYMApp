<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Gym;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ClientService
{
    private const PHOTO_DIRECTORY = 'clients';

    public function __construct(
        private readonly ClientRepositoryInterface $clients,
    ) {}

    public function list(Gym $gym, array $filters): LengthAwarePaginator
    {
        return $this->clients->paginate($gym, $filters);
    }

    public function find(Gym $gym, int $id): Client
    {
        return $this->clients->findForGym($gym, $id);
    }

    public function create(Gym $gym, array $data, ?UploadedFile $photo): Client
    {
        $registrationStart = Carbon::today();

        $attributes = [
            'gym_id' => $gym->id,
            'coach_id' => $data['coach_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'age' => $data['age'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'registration_type' => $data['registration_type'],
            'registration_start' => $registrationStart,
            'registration_end' => $this->calculateRegistrationEnd($data['registration_type'], $registrationStart),
            'photo' => $photo ? $this->storePhoto($photo) : null,
        ];

        return $this->clients->create($attributes, $data['sports']);
    }

    public function update(Client $client, array $data, ?UploadedFile $photo): Client
    {
        $attributes = array_intersect_key($data, array_flip([
            'first_name', 'last_name', 'age', 'email', 'phone', 'coach_id', 'email_reminder_enabled',
        ]));

        if (isset($data['registration_type'])) {
            $attributes['registration_type'] = $data['registration_type'];

            if ($data['registration_type'] !== $client->registration_type) {
                $attributes['registration_end'] = $this->calculateRegistrationEnd(
                    $data['registration_type'],
                    $client->registration_start,
                );
            }
        }

        if ($photo) {
            $this->deletePhoto($client->photo);
            $attributes['photo'] = $this->storePhoto($photo);
        }

        return $this->clients->update($client, $attributes, $data['sports'] ?? null);
    }

    public function delete(Client $client): void
    {
        $this->clients->delete($client);
    }

    private function calculateRegistrationEnd(string $registrationType, Carbon $start): Carbon
    {
        $start = $start->copy();

        return match ($registrationType) {
            'day' => $start->addDay(),
            'week' => $start->addWeek(),
            'month' => $start->addMonth(),
            'year' => $start->addYear(),
        };
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
