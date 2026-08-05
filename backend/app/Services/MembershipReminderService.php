<?php

namespace App\Services;

use App\Jobs\SendMembershipReminderJob;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MembershipReminderService
{
    /**
     * Reminders are sent exactly this many days before the membership expires.
     */
    private const REMINDER_DAYS_BEFORE_EXPIRY = 3;

    public function __construct(
        private readonly ClientRepositoryInterface $clients,
    ) {}

    /**
     * Find clients whose membership expires in exactly
     * self::REMINDER_DAYS_BEFORE_EXPIRY days and queue a reminder email for each.
     *
     * Expired memberships, clients with reminders already sent, and clients
     * who disabled reminders are excluded by the repository query itself.
     */
    public function dispatchDueReminders(): int
    {
        $expiresOn = Carbon::today()->addDays(self::REMINDER_DAYS_BEFORE_EXPIRY);

        $clients = $this->clients->dueForReminder($expiresOn);

        foreach ($clients as $client) {
            SendMembershipReminderJob::dispatch($client->id);
        }

        Log::info('Membership reminder run completed.', [
            'expires_on' => $expiresOn->toDateString(),
            'clients_queued' => $clients->count(),
        ]);

        return $clients->count();
    }
}
