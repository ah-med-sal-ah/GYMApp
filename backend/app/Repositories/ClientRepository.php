<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\Gym;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository implements ClientRepositoryInterface
{
    private const SORTABLE_COLUMNS = ['first_name', 'age', 'created_at'];

    public function paginate(Gym $gym, array $filters): LengthAwarePaginator
    {
        $query = $gym->clients()->with('sports');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['registration_type'])) {
            $query->where('registration_type', $filters['registration_type']);
        }

        if (! empty($filters['sport_id'])) {
            $query->whereHas('sports', fn ($q) => $q->where('sports.id', $filters['sport_id']));
        }

        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE_COLUMNS, true)
            ? $filters['sort_by']
            : 'created_at';
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->paginate($perPage);
    }

    public function findForGym(Gym $gym, int $id): Client
    {
        return $gym->clients()->with(['sports', 'coach'])->findOrFail($id);
    }

    public function create(array $attributes, array $sportIds): Client
    {
        $client = Client::create($attributes);
        $client->sports()->sync($sportIds);

        return $client->load(['sports', 'coach']);
    }

    public function update(Client $client, array $attributes, ?array $sportIds): Client
    {
        $client->update($attributes);

        if ($sportIds !== null) {
            $client->sports()->sync($sportIds);
        }

        return $client->load(['sports', 'coach']);
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }

    public function countForGym(Gym $gym): int
    {
        return $gym->clients()->count();
    }

    public function allWithSportsForGym(Gym $gym): Collection
    {
        return $gym->clients()->with('sports')->get();
    }

    public function dueForReminder(Carbon $expiresOn): Collection
    {
        return Client::query()
            ->with('gym')
            ->whereDate('registration_end', $expiresOn->toDateString())
            ->where('email_reminder_enabled', true)
            ->whereNull('reminder_sent_at')
            ->get();
    }

    public function markReminderSent(Client $client): void
    {
        $client->update(['reminder_sent_at' => now()]);
    }
}
