<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Runs every day and queues membership expiration reminder emails for
// clients whose registration ends in exactly 3 days.
Schedule::command('reminders:send-membership-expiration')
    ->daily()
    ->withoutOverlapping();
