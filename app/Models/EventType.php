<?php

namespace App\Models;

class EventType
{
    // Массив доступных типов мероприятий
    public static $types = [
        'lecture' => 'Лекция',
        'seminar' => 'Семинар',
        'workshop' => 'Мастер-класс',
        'conference' => 'Конференция',
        'competition' => 'Соревнование',
        'exhibition' => 'Выставка',
        'concert' => 'Концерт',
        'meeting' => 'Встреча',
        'excursion' => 'Экскурсия',
        'other' => 'Другое'
    ];

    // Получить все типы мероприятий
    public static function all()
    {
        return self::$types;
    }

    // Получить название типа по ключу
    public static function getName($key)
    {
        return self::$types[$key] ?? $key;
    }
}
