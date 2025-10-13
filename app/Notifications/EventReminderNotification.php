<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;
    protected $hoursUntilEvent;

    /**
     * Create a new notification instance.
     */
    public function __construct(Event $event, int $hoursUntilEvent)
    {
        $this->event = $event;
        $this->hoursUntilEvent = $hoursUntilEvent;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $eventUrl = url('/events/' . $this->event->id);
        
        return (new MailMessage)
            ->subject('Напоминание о мероприятии: ' . $this->event->title)
            ->greeting('Здравствуйте, ' . $notifiable->name . '!')
            ->line('Напоминаем, что вы зарегистрированы на мероприятие, которое состоится через ' . $this->hoursUntilEvent . ' ' . $this->pluralizeHours($this->hoursUntilEvent) . '.')
            ->line('Название мероприятия: ' . $this->event->title)
            ->line('Дата и время: ' . $this->event->date_time->format('d.m.Y H:i'))
            ->line('Место проведения: ' . $this->event->location)
            ->action('Подробнее о мероприятии', $eventUrl)
            ->line('Ждем вас на мероприятии!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_date_time' => $this->event->date_time->format('Y-m-d H:i:s'),
            'hours_until_event' => $this->hoursUntilEvent,
        ];
    }
    
    /**
     * Pluralize hours in Russian.
     */
    private function pluralizeHours(int $hours): string
    {
        $mod10 = $hours % 10;
        $mod100 = $hours % 100;
        
        if ($mod10 === 1 && $mod100 !== 11) {
            return 'час';
        }
        
        if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
            return 'часа';
        }
        
        return 'часов';
    }
}
