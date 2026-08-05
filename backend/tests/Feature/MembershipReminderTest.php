<?php

namespace Tests\Feature;

use App\Jobs\SendMembershipReminderJob;
use App\Mail\MembershipExpirationReminderMail;
use App\Models\Client;
use App\Models\Gym;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Services\MembershipReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MembershipReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-05');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function clientExpiringIn(Gym $gym, int $days, array $overrides = []): Client
    {
        return Client::factory()->for($gym)->create(array_merge([
            'registration_start' => Carbon::today()->subDays(30 - $days),
            'registration_end' => Carbon::today()->addDays($days),
        ], $overrides));
    }

    public function test_the_scheduler_command_is_registered(): void
    {
        Artisan::call('list');

        $this->assertStringContainsString(
            'reminders:send-membership-expiration',
            Artisan::output(),
        );
    }

    public function test_it_queues_a_reminder_job_for_clients_expiring_in_exactly_three_days(): void
    {
        Bus::fake();

        $gym = Gym::factory()->create();
        $client = $this->clientExpiringIn($gym, 3);

        Artisan::call('reminders:send-membership-expiration');

        Bus::assertDispatched(SendMembershipReminderJob::class, fn ($job) => $job->clientId === $client->id);
    }

    public function test_it_does_not_queue_a_reminder_before_three_days_remaining(): void
    {
        Bus::fake();

        $gym = Gym::factory()->create();
        $this->clientExpiringIn($gym, 5);

        Artisan::call('reminders:send-membership-expiration');

        Bus::assertNotDispatched(SendMembershipReminderJob::class);
    }

    public function test_it_does_not_queue_a_reminder_for_an_expired_membership(): void
    {
        Bus::fake();

        $gym = Gym::factory()->create();
        Client::factory()->for($gym)->create([
            'registration_start' => Carbon::today()->subDays(10),
            'registration_end' => Carbon::today()->subDays(1),
        ]);

        Artisan::call('reminders:send-membership-expiration');

        Bus::assertNotDispatched(SendMembershipReminderJob::class);
    }

    public function test_it_does_not_queue_a_reminder_when_already_sent(): void
    {
        Bus::fake();

        $gym = Gym::factory()->create();
        $this->clientExpiringIn($gym, 3, ['reminder_sent_at' => now()->subDay()]);

        Artisan::call('reminders:send-membership-expiration');

        Bus::assertNotDispatched(SendMembershipReminderJob::class);
    }

    public function test_it_does_not_queue_a_reminder_when_disabled_for_the_client(): void
    {
        Bus::fake();

        $gym = Gym::factory()->create();
        $this->clientExpiringIn($gym, 3, ['email_reminder_enabled' => false]);

        Artisan::call('reminders:send-membership-expiration');

        Bus::assertNotDispatched(SendMembershipReminderJob::class);
    }

    public function test_running_the_service_twice_only_queues_the_reminder_once(): void
    {
        $gym = Gym::factory()->create();
        $client = $this->clientExpiringIn($gym, 3);

        Mail::fake();

        /** @var MembershipReminderService $service */
        $service = app(MembershipReminderService::class);

        $firstRunCount = $service->dispatchDueReminders();
        // Simulate the job having run and marked the reminder as sent.
        app(ClientRepositoryInterface::class)->markReminderSent($client->fresh());

        $secondRunCount = $service->dispatchDueReminders();

        $this->assertSame(1, $firstRunCount);
        $this->assertSame(0, $secondRunCount);
    }

    public function test_the_job_sends_the_email_from_the_gym_and_marks_the_reminder_sent(): void
    {
        Mail::fake();

        $gym = Gym::factory()->create(['name' => 'Power Gym', 'email' => 'powergym@example.com']);
        $client = $this->clientExpiringIn($gym, 3);

        (new SendMembershipReminderJob($client->id))->handle(app(ClientRepositoryInterface::class));

        Mail::assertSent(MembershipExpirationReminderMail::class, function ($mail) use ($client, $gym) {
            return $mail->hasTo($client->email)
                && $mail->client->is($client)
                && $mail->gym->is($gym)
                && $mail->envelope()->from->address === $gym->email;
        });

        $this->assertNotNull($client->fresh()->reminder_sent_at);
    }

    public function test_the_job_does_not_resend_if_already_sent(): void
    {
        Mail::fake();

        $gym = Gym::factory()->create();
        $client = $this->clientExpiringIn($gym, 3, ['reminder_sent_at' => now()]);

        (new SendMembershipReminderJob($client->id))->handle(app(ClientRepositoryInterface::class));

        Mail::assertNothingSent();
    }

    public function test_the_job_does_not_send_when_reminders_are_disabled(): void
    {
        Mail::fake();

        $gym = Gym::factory()->create();
        $client = $this->clientExpiringIn($gym, 3, ['email_reminder_enabled' => false]);

        (new SendMembershipReminderJob($client->id))->handle(app(ClientRepositoryInterface::class));

        Mail::assertNothingSent();
        $this->assertNull($client->fresh()->reminder_sent_at);
    }
}
