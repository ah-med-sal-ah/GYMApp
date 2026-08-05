<?php

namespace App\Console\Commands;

use App\Services\MembershipReminderService;
use Illuminate\Console\Command;

class SendMembershipReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-membership-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue reminder emails for clients whose membership expires in 3 days.';

    public function handle(MembershipReminderService $reminderService): int
    {
        $count = $reminderService->dispatchDueReminders();

        $this->info("Queued {$count} membership expiration reminder email(s).");

        return self::SUCCESS;
    }
}
