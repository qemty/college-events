<?php

namespace App\Console\Commands;

use App\Jobs\SendEventReminderEmails;
use App\Jobs\SendEventHourReminderEmails;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:send-reminders {type=all : Тип напоминаний (day, hour, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправить напоминания о предстоящих мероприятиях (за сутки и/или за час)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        
        if ($type === 'day' || $type === 'all') {
            $this->info('Отправка напоминаний за сутки до мероприятия...');
            dispatch(new SendEventReminderEmails());
            $this->info('Напоминания за сутки поставлены в очередь.');
        }
        
        if ($type === 'hour' || $type === 'all') {
            $this->info('Отправка напоминаний за час до мероприятия...');
            dispatch(new SendEventHourReminderEmails());
            $this->info('Напоминания за час поставлены в очередь.');
        }
        
        $this->info('Для обработки очереди выполните: php artisan queue:work');
        
        return Command::SUCCESS;
    }
}
