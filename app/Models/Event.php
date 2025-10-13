<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date_time',
        'location',
        'max_participants',
        'type',
        'theme',
        'attendance_token',
    ];

    protected $dates = [
        'date_time',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'date_time' => 'datetime',
    ];

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
    public function eventGroups()
    {
        return $this->hasMany(EventGroup::class);
    }
    
    // Получить группы, для которых доступно мероприятие
    public function groups()
    {
        return $this->eventGroups()->pluck('group')->toArray();
    }
    
    // Проверить, доступно ли мероприятие для указанной группы
    public function isAvailableForGroup($group)
    {
        // Если у мероприятия нет привязанных групп, оно доступно всем
        if ($this->eventGroups()->count() === 0) {
            return true;
        }
        
        return $this->eventGroups()->where('group', $group)->exists();
    }
    
    // Синхронизировать группы мероприятия
    public function syncGroups(array $groups)
    {
        // Удаляем все текущие связи
        $this->eventGroups()->delete();
        
        // Добавляем новые связи
        foreach ($groups as $group) {
            if (!empty($group)) {
                $this->eventGroups()->create(['group' => $group]);
            }
        }
    }
}
