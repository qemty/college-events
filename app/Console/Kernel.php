<?php

namespace App\Console;

use App\Jobs\SendEventReminderEmails;
use App\Jobs\SendEventHourReminderEmails;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Запускаем задачу отправки уведомлений за сутки каждый день в 8 утра
        $schedule->job(new SendEventReminderEmails)->dailyAt('08:00');
        
        // Запускаем задачу отправки уведомлений за час каждый час
        $schedule->job(new SendEventHourReminderEmails)->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}