<?php

namespace App\Jobs;

use App\Mail\MembershipExpirationReminderMail;
use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendMembershipReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly int $clientId,
    ) {}

    public function handle(ClientRepositoryInterface $clients): void
    {
        /** @var Client|null $client */
        $client = Client::query()->with('gym')->find($this->clientId);

        if (! $client || ! $client->gym) {
            return;
        }

        // Re-check eligibility at execution time in case state changed
        // between dispatch and processing (e.g. reminder already sent).
        if (! $client->email_reminder_enabled || $client->reminder_sent_at !== null) {
            return;
        }

        $remainingDays = (int) now()->startOfDay()->diffInDays($client->registration_end->copy()->startOfDay(), false);

        try {
            Mail::to($client->email)->send(
                new MembershipExpirationReminderMail($client, $client->gym, $remainingDays)
            );

            $clients->markReminderSent($client);

            Log::info('Membership reminder email sent.', [
                'gym_id' => $client->gym_id,
                'client_id' => $client->id,
                'email' => $client->email,
                'date' => now()->toDateTimeString(),
                'status' => 'success',
            ]);
        } catch (Throwable $e) {
            Log::error('Membership reminder email failed to send.', [
                'gym_id' => $client->gym_id,
                'client_id' => $client->id,
                'email' => $client->email,
                'date' => now()->toDateTimeString(),
                'status' => 'failure',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Membership reminder job permanently failed after retries.', [
            'client_id' => $this->clientId,
            'date' => now()->toDateTimeString(),
            'status' => 'failure',
            'error' => $exception->getMessage(),
        ]);
    }
}
