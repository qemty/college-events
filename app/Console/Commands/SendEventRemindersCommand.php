<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Registration;
use App\Notifications\EventReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendEventRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:send-reminders {--hours=24 : Hours before event to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders about upcoming events to registered users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $this->info("Sending reminders for events happening in {$hours} hours...");
        
        // Получаем время для фильтрации событий
        $targetTime = Carbon::now()->addHours($hours);
        $startTime = (clone $targetTime)->subMinutes(30);
        $endTime = (clone $targetTime)->addMinutes(30);
        
        // Находим события, которые начнутся через указанное количество часов (с погрешностью ±30 минут)
        $events = Event::whereBetween('date_time', [$startTime, $endTime])
            ->where('date_time', '>', Carbon::now())
            ->get();
            
        $this->info("Found {$events->count()} upcoming events.");
        
        $notificationCount = 0;
        
        foreach ($events as $event) {
            // Получаем всех зарегистрированных пользователей для этого события
            $registrations = Registration::where('event_id', $event->id)
                ->with('user')
                ->get();
                
            $this->info("Event: {$event->title} - {$registrations->count()} registrations.");
            
            foreach ($registrations as $registration) {
        $user = $registration->user;
        
        if (!$user || !$user->email_notifications) {
            continue; // Пропускаем пользователей, отключивших уведомления
        }
                
                // Отправляем уведомление
                try {
                    $hoursUntilEvent = Carbon::now()->diffInHours($event->date_time, false);
                    $user->notify(new EventReminderNotification($event, $hoursUntilEvent));
                    $notificationCount++;
                    
                    $this->info("Sent reminder to {$user->email} for event {$event->title}");
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder to {$user->email}: {$e->getMessage()}");
                    Log::error("Failed to send event reminder: {$e->getMessage()}", [
                        'user_id' => $user->id,
                        'event_id' => $event->id,
                        'exception' => $e,
                    ]);
                }
            }
        }
        
        $this->info("Sent {$notificationCount} reminders in total.");
        
        return Command::SUCCESS;
    }
}
