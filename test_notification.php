<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Получите конкретное мероприятие и пользователя
$event = \App\Models\Event::find(28);
$user = \App\Models\User::find(51);

// Отправьте уведомление
$user->notify(new \App\Notifications\EventReminderNotification($event, 1)); // или 24 для суточного уведомления

echo "Уведомление отправлено для мероприятия {$event->title} пользователю {$user->name}\n";
