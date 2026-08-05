<?php

namespace Tests\Unit;

use App\Models\Client;
use Tests\TestCase;

class ClientReminderStatusTest extends TestCase
{
    public function test_status_is_disabled_when_reminders_are_turned_off(): void
    {
        $client = new Client(['email_reminder_enabled' => false]);

        $this->assertSame('disabled', $client->reminderStatus());
    }

    public function test_status_is_pending_when_enabled_and_not_yet_sent(): void
    {
        $client = new Client(['email_reminder_enabled' => true]);

        $this->assertSame('pending', $client->reminderStatus());
    }

    public function test_status_is_sent_when_reminder_sent_at_is_set(): void
    {
        $client = new Client(['email_reminder_enabled' => true]);
        $client->reminder_sent_at = now();

        $this->assertSame('sent', $client->reminderStatus());
    }
}
