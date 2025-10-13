<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'event_id', 'attended'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Проверка уникальности записи посещаемости
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($attendance) {
            // Проверяем, нет ли уже записи для этого пользователя и мероприятия
            if (self::where('user_id', $attendance->user_id)
                    ->where('event_id', $attendance->event_id)
                    ->exists()) {
                throw new \Exception('Запись посещаемости для этого пользователя и мероприятия уже существует.');
            }
        });
    }
}