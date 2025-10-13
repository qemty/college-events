<?php

namespace App\Console\Commands;

use App\Jobs\SendEventReminderEmails;
use Illuminate\Console\Command;

class SendEmailReminders extends Command
{
    protected $signature = 'email:send-reminders';
    protected $description = 'Send reminder emails for upcoming events';

    public function handle()
    {
        SendEventReminderEmails::dispatch();
        $this->info('Reminder emails dispatched successfully.');
    }
}