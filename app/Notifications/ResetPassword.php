<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Notification
{
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject(__('Уведомление о сбросе пароля'))
            ->line(__('Вы получили это письмо, потому что мы получили запрос на сброс пароля для вашей учетной записи.'))
            ->action(__('Сбросить пароль'), $url)
            ->line(__('Срок действия этой ссылки для сброса пароля истекает через :count минут.', ['count' => config('auth.passwords.users.expire')]))
            ->line(__('Если вы не запрашивали сброс пароля, никаких дальнейших действий не требуется.'));
    }
}