<?php

namespace App\Repositories\Contracts;

use App\Models\Client;
use App\Models\Gym;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ClientRepositoryInterface
{
    public function paginate(Gym $gym, array $filters): LengthAwarePaginator;

    public function findForGym(Gym $gym, int $id): Client;

    public function create(array $attributes, array $sportIds): Client;

    public function update(Client $client, array $attributes, ?array $sportIds): Client;

    public function delete(Client $client): void;

    public function countForGym(Gym $gym): int;

    public function allWithSportsForGym(Gym $gym): Collection;

    /**
     * Clients whose membership expires exactly on the given date, still active,
     * with reminders enabled and not yet sent.
     */
    public function dueForReminder(Carbon $expiresOn): Collection;

    public function markReminderSent(Client $client): void;
}
