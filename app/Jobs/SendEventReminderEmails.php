<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Registration;
use App\Notifications\EventReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEventReminderEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Находим мероприятия, которые начинаются завтра
        $tomorrow = now()->addDay()->startOfDay();
        $events = Event::whereDate('date_time', $tomorrow)->get();

        foreach ($events as $event) {
            // Получаем зарегистрированных студентов
            $registrations = Registration::where('event_id', $event->id)->with('user')->get();

            foreach ($registrations as $registration) {
                // Проверяем, включены ли уведомления для пользователя
                if ($registration->user && $registration->user->email_notifications) {
                    // Отправляем уведомление с указанием, что до мероприятия 24 часа
                    $registration->user->notify(new EventReminderNotification($event, 24));
                }
            }
        }
    }
}