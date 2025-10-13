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

class SendEventHourReminderEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Находим мероприятия, которые начинаются через час (с небольшим запасом)
        $oneHourLater = now()->addHour();
        $events = Event::whereBetween('date_time', [
            $oneHourLater->copy()->subMinutes(5),
            $oneHourLater->copy()->addMinutes(5)
        ])->get();

        foreach ($events as $event) {
            // Получаем зарегистрированных студентов
            $registrations = Registration::where('event_id', $event->id)->with('user')->get();

            foreach ($registrations as $registration) {
                // Проверяем, включены ли уведомления для пользователя
                if ($registration->user && $registration->user->email_notifications) {
                    // Отправляем уведомление с указанием, что до мероприятия 1 час
                    $registration->user->notify(new EventReminderNotification($event, 1));
                }
            }
        }
    }
}
