<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'group', 'curator_groups', 'email_notifications',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'curator_groups' => 'array',
        'email_notifications' => 'boolean',
    ];
    
    // Геттер для проверки, является ли пользователь куратором
    public function isCurator()
    {
        return $this->role === 'curator';
    }
    
    // Геттер для проверки, является ли пользователь администратором
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    // Геттер для проверки, является ли пользователь студентом
    public function isStudent()
    {
        return $this->role === 'student';
    }
    
    // Проверка, имеет ли пользователь доступ к группе
    public function hasAccessToGroup($group)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        if ($this->isCurator() && is_array($this->curator_groups)) {
            return in_array($group, $this->curator_groups);
        }
        
        if ($this->isStudent()) {
            return $this->group === $group;
        }
        
        return false;
    }
}
